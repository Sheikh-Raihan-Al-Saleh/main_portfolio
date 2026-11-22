<?php

namespace App\Services\LandingPage;

use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Models\Studio;
use App\Services\Media\MediaService;
use App\Services\Studio\StudioService;
use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

/**
 * Content blocks of the studio home page and of project landing pages.
 */
class LandingSectionService
{
    /** Block fields saved to the table (the type is set once, on create). */
    private const FIELDS = ['eyebrow', 'heading', 'subheading', 'body', 'data', 'is_visible'];

    public function __construct(
        private readonly MediaService $mediaService,
        private readonly StudioService $studioService,
    ) {}

    /**
     * Visible blocks of a page, in order, for the public site.
     *
     * @return Collection<int, LandingSection>
     */
    public function visibleFor(Studio|ProjectLandingPage $owner): Collection
    {
        return $owner->sections()->visible()->ordered()->get();
    }

    /**
     * Every block of a page, in order, for the admin builder.
     *
     * @return Collection<int, LandingSection>
     */
    public function allFor(Studio|ProjectLandingPage $owner): Collection
    {
        return $owner->sections()->ordered()->get();
    }

    /**
     * The page a new block goes on: the studio home page, or a project's
     * landing page (created on demand, so blocks can be added before the
     * page itself is saved).
     */
    public function ownerFor(string $owner, ?int $projectId): Studio|ProjectLandingPage
    {
        if ($owner === 'studio') {
            return $this->studioService->current();
        }

        return Project::query()->findOrFail($projectId)->landingPage()->firstOrCreate([]);
    }

    /**
     * Add a block at the end of its page.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(Studio|ProjectLandingPage $owner, array $data): LandingSection
    {
        $section = new LandingSection(Arr::only($data, [...self::FIELDS, 'type']));
        $section->sort_order = (int) $owner->sections()->max('sort_order') + 1;
        $owner->sections()->save($section);

        return $section;
    }

    /**
     * Update a block and delete any file the edit dropped from it.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(LandingSection $section, array $data): LandingSection
    {
        $oldFiles = $section->mediaPaths();

        $section->update(Arr::only($data, self::FIELDS));

        $this->mediaService->deleteMany(array_diff($oldFiles, $section->mediaPaths()));

        return $section;
    }

    /**
     * Delete a block and its files.
     */
    public function delete(LandingSection $section): void
    {
        $files = $section->mediaPaths();

        $section->delete();

        $this->mediaService->deleteMany($files);
    }

    /**
     * Save a new block order. Only blocks of the same page as the first ID
     * are moved.
     *
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        $first = LandingSection::query()->find($ids[0] ?? null);

        if ($first === null) {
            return;
        }

        SortOrder::apply(
            LandingSection::query()
                ->where('sectionable_type', $first->sectionable_type)
                ->where('sectionable_id', $first->sectionable_id),
            $ids,
        );
    }

    /**
     * Store an image or video for a block before the block is saved.
     *
     * @return array{path: string, url: string|null}
     */
    public function uploadMedia(UploadedFile $file, string $folder): array
    {
        $path = $this->mediaService->store($file, 'landing/'.$folder);

        return ['path' => $path, 'url' => MediaService::url($path)];
    }
}
