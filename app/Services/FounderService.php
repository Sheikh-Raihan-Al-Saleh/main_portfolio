<?php

namespace App\Services;

use App\Models\Founder;
use App\Models\SkillCategory;
use App\Support\FounderDefaults;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The founder record and the personal portfolio page built from it.
 */
class FounderService
{
    public function __construct(
        private readonly StudioService $studios,
        private readonly MediaService $media,
        private readonly SocialLinkService $socialLinks,
    ) {}

    /**
     * The founder, with social links. Created from defaults (with the default
     * skill categories) on first use. Memoised per request.
     */
    public function current(): Founder
    {
        return once(function (): Founder {
            $founder = Founder::query()->with('socialLinks')->orderBy('id')->first();

            return $founder ?? $this->createDefault();
        });
    }

    /**
     * Everything the /founder page renders, eager loaded in a fixed number of
     * queries regardless of how much content there is.
     */
    public function loadPortfolio(Founder $founder): Founder
    {
        return $founder->load([
            'skillCategories' => fn ($query) => $query->ordered()
                ->whereHas('skills')
                ->with(['skills' => fn ($skills) => $skills->ordered()]),
            'experiences' => fn ($query) => $query->ordered()->with('highlights'),
            'educations' => fn ($query) => $query->ordered(),
        ]);
    }

    /**
     * Headline numbers for the founder hero. Expects loadPortfolio() first.
     *
     * @return array{projects: int, skills: int, yearsExperience: int}
     */
    public function stats(Founder $founder, int $publishedProjects): array
    {
        $earliest = $founder->experiences->min('start_date');

        return [
            'projects' => $publishedProjects,
            'skills' => $founder->skillCategories->sum(fn (SkillCategory $category): int => $category->skills->count()),
            'yearsExperience' => $earliest === null ? 0 : (int) $earliest->diffInYears(now(), absolute: true),
        ];
    }

    /**
     * Save the admin "Founder" form.
     *
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function update(array $data): Founder
    {
        $founder = $this->current();

        DB::transaction(function () use ($founder, $data): void {
            $founder->fill(collect($data)->only([
                'name', 'headline', 'tagline', 'bio', 'founder_message', 'roles',
                'is_available_for_work', 'location', 'public_email', 'phone',
                'hero_title', 'hero_statement',
                'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
                'footer', 'content', 'meta_title', 'meta_description',
            ])->all());

            $founder->slug = Str::slug($founder->name) ?: 'founder';

            foreach ([
                'avatar' => ['avatar_path', 'founder'],
                'logo' => ['logo_path', 'founder'],
                'og_image' => ['og_image_path', 'founder'],
                'resume' => ['resume_path', 'founder/resume'],
            ] as $input => [$column, $directory]) {
                $founder->{$column} = $this->media->replace(
                    $data[$input] ?? null,
                    $directory,
                    $founder->{$column},
                    (bool) ($data['remove_'.$input] ?? false),
                );
            }

            $founder->save();

            if (array_key_exists('socials', $data)) {
                $this->socialLinks->sync($founder, (array) $data['socials']);
            }
        });

        return $founder;
    }

    /**
     * Stream the uploaded resume under a stable, readable filename.
     */
    public function resumeResponse(): ?StreamedResponse
    {
        $founder = $this->current();

        if (! $this->media->exists($founder->resume_path)) {
            return null;
        }

        return Storage::disk(MediaService::DISK)->response(
            (string) $founder->resume_path,
            Str::slug($founder->name).'-resume.pdf',
        );
    }

    private function createDefault(): Founder
    {
        return DB::transaction(function (): Founder {
            $founder = new Founder(FounderDefaults::attributes());
            $founder->studio_id = $this->studios->current()->id;
            $founder->save();

            $position = 0;

            foreach (SkillCategory::DEFAULTS as $slug => $name) {
                $founder->skillCategories()->create([
                    'name' => $name,
                    'slug' => $slug,
                    'sort_order' => $position++,
                ]);
            }

            return $founder->load('socialLinks');
        });
    }
}
