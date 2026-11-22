<?php

namespace App\Services;

use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use Illuminate\Support\Facades\DB;

/**
 * A project's case-study landing page (/p/{slug}) and its hero media slots.
 */
class LandingPageService
{
    public function __construct(
        private readonly MediaService $media,
    ) {}

    /**
     * The published landing page of a published project, with its hero media
     * and visible sections. Null when either is a draft.
     */
    public function findPublished(Project $project): ?ProjectLandingPage
    {
        if (! $project->is_published) {
            return null;
        }

        return $project->landingPage()
            ->published()
            ->with([
                'heroMedia',
                'sections' => fn ($query) => $query->visible()->ordered(),
            ])
            ->first();
    }

    /**
     * The landing page for the admin builder (all sections), or null before
     * its first save.
     */
    public function findForEdit(Project $project): ?ProjectLandingPage
    {
        return $project->landingPage()
            ->with([
                'heroMedia',
                'sections' => fn ($query) => $query->ordered(),
            ])
            ->first();
    }

    /**
     * Create or update the landing page from the admin form.
     *
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function save(Project $project, array $data): ProjectLandingPage
    {
        return DB::transaction(function () use ($project, $data): ProjectLandingPage {
            $page = $project->landingPage()->firstOrNew();

            $page->fill(collect($data)->only([
                'eyebrow', 'headline', 'subheadline', 'hero_video_url',
                'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
                'accent_from', 'accent_to', 'seo_title', 'seo_description', 'is_published',
            ])->all());

            $page->og_image_path = $this->media->replace(
                $data['og_image'] ?? null,
                'landing/og',
                $page->og_image_path,
                (bool) ($data['remove_og_image'] ?? false),
            );

            $project->landingPage()->save($page);

            $this->saveHeroSlots(
                $page,
                (array) ($data['hero_media'] ?? []),
                (array) ($data['remove_hero_media'] ?? []),
            );

            return $page;
        });
    }

    /**
     * Delete the landing page with its sections and every file they own.
     */
    public function delete(Project $project): void
    {
        $page = $project->landingPage()->with(['heroMedia', 'sections'])->first();

        if ($page === null) {
            return;
        }

        $paths = $this->mediaPaths($page);

        DB::transaction(function () use ($page): void {
            // Sections are polymorphic, so no foreign key cascades them.
            $page->sections()->delete();
            $page->delete();
        });

        $this->media->deleteMany($paths);
    }

    /**
     * Every uploaded file the page owns: hero slots, OG image, section media.
     *
     * @return array<int, string>
     */
    public function mediaPaths(ProjectLandingPage $page): array
    {
        $page->loadMissing(['heroMedia', 'sections']);

        return array_values(array_filter([
            $page->og_image_path,
            ...$page->heroMedia->pluck('path'),
            ...$page->sections->flatMap(fn (LandingSection $section): array => $section->mediaPaths()),
        ]));
    }

    /**
     * Replace or clear individual hero slots (0 desktop, 1 tablet, 2 phone).
     *
     * @param  array<int|string, mixed>  $files  Uploaded files keyed by slot.
     * @param  array<int|string, mixed>  $remove  Truthy flags keyed by slot.
     */
    private function saveHeroSlots(ProjectLandingPage $page, array $files, array $remove): void
    {
        foreach (array_keys(ProjectLandingPage::HERO_SLOTS) as $slot) {
            $file = $files[$slot] ?? null;
            $clear = filter_var($remove[$slot] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($file === null && ! $clear) {
                continue;
            }

            $existing = $page->heroMedia()->where('slot', $slot)->first();

            if ($file !== null) {
                $path = $this->media->store($file, 'landing/hero');
                $page->heroMedia()->updateOrCreate(['slot' => $slot], ['path' => $path]);
            } else {
                $existing?->delete();
            }

            $this->media->delete($existing?->path);
        }
    }
}
