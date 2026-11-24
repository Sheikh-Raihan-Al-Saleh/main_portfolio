<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectLandingPage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Saves and deletes a project's landing page with its hero screenshots,
 * OG image and blocks.
 */
class LandingPageService
{
    /** Form fields saved to the table. */
    private const FIELDS = [
        'eyebrow', 'headline', 'subheadline', 'hero_video_url',
        'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
        'accent_from', 'accent_to', 'seo_title', 'seo_description', 'is_published',
    ];

    public function __construct(private FileService $files) {}

    /**
     * Save a project's landing page. It is created on its first save.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function save(Project $project, array $data): void
    {
        DB::transaction(function () use ($project, $data): void {
            $page = $project->landingPage()->firstOrNew();

            $page->fill(Arr::only($data, self::FIELDS));
            $page->og_image_path = $this->files->replace(
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
        });
    }

    /**
     * Delete a landing page with its blocks and every file it owned. The
     * files are deleted once the rows are gone for good: right away, or after
     * the caller's transaction commits.
     */
    public function delete(ProjectLandingPage $page): void
    {
        $page->loadMissing(['heroMedia', 'sections']);
        $files = $page->filePaths();

        DB::transaction(function () use ($page): void {
            // Blocks are polymorphic, so no foreign key cascades them.
            $page->sections()->delete();
            $page->delete();
        });

        DB::afterCommit(fn () => $this->files->deleteMany($files));
    }

    /**
     * Replace or clear individual hero slots (0 desktop, 1 tablet, 2 phone).
     *
     * @param  array<int|string, mixed>  $uploads  Uploaded files keyed by slot.
     * @param  array<int|string, mixed>  $remove  Truthy flags keyed by slot.
     */
    private function saveHeroSlots(ProjectLandingPage $page, array $uploads, array $remove): void
    {
        foreach (array_keys(ProjectLandingPage::HERO_SLOTS) as $slot) {
            $upload = $uploads[$slot] ?? null;
            $clear = filter_var($remove[$slot] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($upload === null && ! $clear) {
                continue;
            }

            $existing = $page->heroMedia()->where('slot', $slot)->first();

            if ($upload !== null) {
                $page->heroMedia()->updateOrCreate(['slot' => $slot], ['path' => $this->files->store($upload, 'landing/hero')]);
            } else {
                $existing?->delete();
            }

            $this->files->delete($existing?->path);
        }
    }
}
