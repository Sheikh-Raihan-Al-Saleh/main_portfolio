<?php

namespace App\Services\Studio;

use App\Models\Studio;
use App\Services\Media\MediaService;
use App\Services\SocialLink\SocialLinkService;
use App\Support\Defaults\StudioDefaults;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Studio module: the company record, its brand (name + logos) and its
 * home page copy.
 */
class StudioService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'short_name', 'legal_name', 'headline', 'tagline', 'bio', 'mission', 'founded_year', 'is_accepting_projects', 'location', 'public_email', 'phone', 'website_url', 'hero_eyebrow', 'hero_title', 'hero_statement', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'footer', 'meta_title', 'meta_description'];

    private const BRAND_CACHE_KEY = 'studio.brand';

    public function __construct(
        private readonly MediaService $mediaService,
        private readonly SocialLinkService $socialLinkService,
    ) {}

    /**
     * The studio with its social links. Created from the defaults on first
     * use, and loaded once per request.
     */
    public function current(): Studio
    {
        return once(fn (): Studio => Studio::query()->with('socialLinks')->orderBy('id')->first()
            ?? Studio::query()->create(StudioDefaults::attributes())->load('socialLinks'));
    }

    /**
     * The company name and logos shown on every page. Cached until the
     * studio is saved again.
     *
     * @return array{name: string, short_name: string, tagline: string|null, logo_url: string|null, logo_dark_url: string|null, logo_mark_url: string|null, website_url: string|null}
     */
    public function brand(): array
    {
        return Cache::rememberForever(self::BRAND_CACHE_KEY, function (): array {
            $studio = $this->current();

            return [
                'name' => $studio->name,
                'short_name' => $studio->short_name ?: $studio->name,
                'tagline' => $studio->tagline,
                'logo_url' => $studio->logo_url,
                'logo_dark_url' => $studio->logo_dark_url,
                'logo_mark_url' => $studio->logo_mark_url,
                'website_url' => $studio->website_url,
            ];
        });
    }

    /**
     * Headline numbers for the public pages. A zero becomes null, so a page
     * never prints "0 clients".
     *
     * @return array{projects: int|null, clients: int|null, yearsInBusiness: int|null}
     */
    public function stats(int $publishedProjects, int $visibleClients): array
    {
        $founded = $this->current()->founded_year;

        return [
            'projects' => $publishedProjects ?: null,
            'clients' => $visibleClients ?: null,
            'yearsInBusiness' => $founded && $founded <= now()->year ? now()->year - $founded : null,
        ];
    }

    /**
     * Save the admin "Studio & brand" form: details, logos and social links.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): Studio
    {
        $studio = $this->current();

        DB::transaction(function () use ($studio, $data): void {
            $studio->fill(Arr::only($data, self::FIELDS));
            $studio->slug = Str::slug($studio->name) ?: 'studio';

            foreach (['logo', 'logo_dark', 'logo_mark', 'og_image'] as $file) {
                $column = $file.'_path';
                $studio->{$column} = $this->mediaService->replace(
                    $data[$file] ?? null,
                    'brand',
                    $studio->{$column},
                    (bool) ($data['remove_'.$file] ?? false),
                );
            }

            $studio->save();

            if (isset($data['socials'])) {
                $this->socialLinkService->sync($studio, $data['socials']);
            }
        });

        Cache::forget(self::BRAND_CACHE_KEY);

        return $studio;
    }

    /**
     * Save the home page's fixed-section copy. It is merged over the
     * defaults (StudioContent) whenever it is read.
     *
     * @param  array<string, mixed>  $content
     */
    public function updateContent(array $content): void
    {
        $studio = $this->current();
        $studio->content = $content;
        $studio->save();
    }

    /**
     * Forget the cached brand, e.g. after logos are changed outside update().
     */
    public function forgetBrand(): void
    {
        Cache::forget(self::BRAND_CACHE_KEY);
    }
}
