<?php

namespace App\Services;

use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

/**
 * Composable content blocks for the studio home page and project landing pages.
 */
class LandingSectionService
{
    public function __construct(
        private readonly MediaService $media,
        private readonly StudioService $studios,
    ) {}

    /**
     * Visible sections of a page, in order, for the public renderer.
     *
     * @return Collection<int, LandingSection>
     */
    public function visibleFor(Studio|ProjectLandingPage $owner): Collection
    {
        return $owner->sections()->visible()->ordered()->get();
    }

    /**
     * Every section of a page, in order, for the admin builder.
     *
     * @return Collection<int, LandingSection>
     */
    public function allFor(Studio|ProjectLandingPage $owner): Collection
    {
        return $owner->sections()->ordered()->get();
    }

    /**
     * Resolve the page a new section is added to.
     *
     * @param  array<string, mixed>  $data  Validated data with `owner` and `project_id`.
     */
    public function owner(array $data): Studio|ProjectLandingPage
    {
        if ($data['owner'] === 'studio') {
            return $this->studios->current();
        }

        $project = Project::query()->findOrFail((int) $data['project_id']);

        // The builder can add a block before the page itself has been saved.
        return $project->landingPage()->firstOrCreate([]);
    }

    /**
     * Append a section at the end of its page.
     *
     * @param  array<string, mixed>  $data  Validated section data.
     */
    public function create(Studio|ProjectLandingPage $owner, array $data): LandingSection
    {
        $section = new LandingSection(collect($data)->only([
            'type', 'eyebrow', 'heading', 'subheading', 'body', 'data', 'is_visible',
        ])->all());

        $section->sort_order = (int) $owner->sections()->max('sort_order') + 1;

        $owner->sections()->save($section);

        return $section;
    }

    /**
     * Update a section and delete any file the edit dropped from its payload.
     *
     * @param  array<string, mixed>  $data  Validated section data.
     */
    public function update(LandingSection $section, array $data): LandingSection
    {
        $previousMedia = $section->mediaPaths();

        $section->fill(collect($data)->only([
            'eyebrow', 'heading', 'subheading', 'body', 'data', 'is_visible',
        ])->all());
        $section->save();

        $this->media->deleteMany(array_diff($previousMedia, $section->mediaPaths()));

        return $section;
    }

    public function delete(LandingSection $section): void
    {
        $paths = $section->mediaPaths();

        $section->delete();

        $this->media->deleteMany($paths);
    }

    /**
     * Store one image or video for a section payload, before the section is
     * saved, and return where it went.
     *
     * @return array{path: string, url: string|null}
     */
    public function uploadMedia(UploadedFile $file, string $directory): array
    {
        $path = $this->media->store($file, 'landing/'.$directory);

        return ['path' => $path, 'url' => MediaService::url($path)];
    }
}
