<?php

namespace App\Services;

use App\Models\Studio;
use App\Support\StudioDefaults;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The studio (company) record: reading it, branding with it, editing it.
 */
class StudioService
{
    private const BRAND_CACHE_KEY = 'studio.brand';

    public function __construct(
        private readonly MediaService $media,
        private readonly SocialLinkService $socialLinks,
    ) {}

    /**
     * The studio, with its social links. Created from defaults on first use,
     * so no page ever has to handle a missing studio. Memoised per request.
     */
    public function current(): Studio
    {
        return once(function (): Studio {
            $studio = Studio::query()->with('socialLinks')->orderBy('id')->first();

            return $studio ?? Studio::query()->create(StudioDefaults::attributes())->load('socialLinks');
        });
    }

    /**
     * The company name and logos used on every page (header, footer, loader,
     * login, admin sidebar, favicon, page titles, emails).
     *
     * Cached until the studio is next saved.
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
     * Whole years since the studio was founded, or null when unknown.
     */
    public function yearsInBusiness(Studio $studio): ?int
    {
        $founded = $studio->founded_year;

        if ($founded === null || $founded > now()->year) {
            return null;
        }

        return now()->year - $founded;
    }

    /**
     * Save the admin "Studio" form.
     *
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function update(array $data): Studio
    {
        $studio = $this->current();

        DB::transaction(function () use ($studio, $data): void {
            $studio->fill(collect($data)->only([
                'name', 'short_name', 'legal_name', 'headline', 'tagline', 'bio', 'mission',
                'founded_year', 'is_accepting_projects', 'location', 'public_email', 'phone',
                'website_url', 'hero_eyebrow', 'hero_title', 'hero_statement',
                'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
                'footer', 'meta_title', 'meta_description',
            ])->all());

            $studio->slug = Str::slug($studio->name) ?: 'studio';

            foreach ([
                'logo' => 'logo_path',
                'logo_dark' => 'logo_dark_path',
                'logo_mark' => 'logo_mark_path',
                'og_image' => 'og_image_path',
            ] as $input => $column) {
                $studio->{$column} = $this->media->replace(
                    $data[$input] ?? null,
                    'brand',
                    $studio->{$column},
                    (bool) ($data['remove_'.$input] ?? false),
                );
            }

            $studio->save();

            if (array_key_exists('socials', $data)) {
                $this->socialLinks->sync($studio, (array) $data['socials']);
            }
        });

        $this->forgetBrand();

        return $studio;
    }

    /**
     * Save the home page's fixed-section copy. Only the validated shape is
     * stored; reads merge it over StudioContent defaults.
     *
     * @param  array<string, mixed>  $content
     */
    public function updateContent(array $content): void
    {
        $studio = $this->current();
        $studio->content = $content;
        $studio->save();
    }

    public function forgetBrand(): void
    {
        Cache::forget(self::BRAND_CACHE_KEY);
    }
}
