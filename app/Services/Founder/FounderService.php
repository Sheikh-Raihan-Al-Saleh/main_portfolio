<?php

namespace App\Services\Founder;

use App\Models\Founder;
use App\Models\SkillCategory;
use App\Services\Media\MediaService;
use App\Services\SocialLink\SocialLinkService;
use App\Services\Studio\StudioService;
use App\Support\Defaults\FounderDefaults;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Founder module: the founder record and the personal portfolio page.
 */
class FounderService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'headline', 'tagline', 'bio', 'founder_message', 'roles', 'is_available_for_work', 'location', 'public_email', 'phone', 'hero_title', 'hero_statement', 'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'footer', 'content', 'meta_title', 'meta_description'];

    public function __construct(
        private readonly StudioService $studioService,
        private readonly MediaService $mediaService,
        private readonly SocialLinkService $socialLinkService,
    ) {}

    /**
     * The founder with social links. Created from the defaults (with the
     * default skill categories) on first use, and loaded once per request.
     */
    public function current(): Founder
    {
        return once(fn (): Founder => Founder::query()->with('socialLinks')->orderBy('id')->first()
            ?? $this->createDefault());
    }

    /**
     * The founder with everything the /founder page shows: skill categories
     * with skills, experience with highlights, and education.
     */
    public function withPortfolio(): Founder
    {
        return $this->current()->load([
            'skillCategories' => fn ($query) => $query->ordered()->whereHas('skills')
                ->with(['skills' => fn ($skills) => $skills->ordered()]),
            'experiences' => fn ($query) => $query->ordered()->with('highlights'),
            'educations' => fn ($query) => $query->ordered(),
        ]);
    }

    /**
     * Headline numbers for the founder hero. Expects withPortfolio() data.
     *
     * @return array{projects: int, skills: int, yearsExperience: int}
     */
    public function stats(Founder $founder, int $publishedProjects): array
    {
        $firstJob = $founder->experiences->min('start_date');

        return [
            'projects' => $publishedProjects,
            'skills' => $founder->skillCategories->sum(fn (SkillCategory $category): int => $category->skills->count()),
            'yearsExperience' => $firstJob ? (int) $firstJob->diffInYears(now(), absolute: true) : 0,
        ];
    }

    /**
     * Save the admin "Founder" form: details, media files and social links.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): Founder
    {
        $founder = $this->current();

        DB::transaction(function () use ($founder, $data): void {
            $founder->fill(Arr::only($data, self::FIELDS));
            $founder->slug = Str::slug($founder->name) ?: 'founder';

            foreach (['avatar' => 'founder', 'logo' => 'founder', 'og_image' => 'founder', 'resume' => 'founder/resume'] as $file => $folder) {
                $column = $file.'_path';
                $founder->{$column} = $this->mediaService->replace(
                    $data[$file] ?? null,
                    $folder,
                    $founder->{$column},
                    (bool) ($data['remove_'.$file] ?? false),
                );
            }

            $founder->save();

            if (isset($data['socials'])) {
                $this->socialLinkService->sync($founder, $data['socials']);
            }
        });

        return $founder;
    }

    /**
     * The uploaded resume as a download with a readable filename, or null.
     */
    public function resumeResponse(): ?StreamedResponse
    {
        $founder = $this->current();

        if (! $this->mediaService->exists($founder->resume_path)) {
            return null;
        }

        return Storage::disk(MediaService::DISK)->response(
            (string) $founder->resume_path,
            Str::slug($founder->name).'-resume.pdf',
        );
    }

    /**
     * The first founder row, with the default skill categories.
     */
    private function createDefault(): Founder
    {
        return DB::transaction(function (): Founder {
            $founder = new Founder(FounderDefaults::attributes());
            $founder->studio_id = $this->studioService->current()->id;
            $founder->save();

            $position = 0;

            foreach (SkillCategory::DEFAULTS as $slug => $name) {
                $founder->skillCategories()->create(['name' => $name, 'slug' => $slug, 'sort_order' => $position++]);
            }

            return $founder->load('socialLinks');
        });
    }
}
