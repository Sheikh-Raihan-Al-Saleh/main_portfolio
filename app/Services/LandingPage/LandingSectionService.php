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
        return $owner->sections()->where('is_visible', true)->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * Every block of a page, in order, for the admin builder.
     *
     * @return Collection<int, LandingSection>
     */
    public function allFor(Studio|ProjectLandingPage $owner): Collection
    {
        return $owner->sections()->orderBy('sort_order')->orderBy('id')->get();
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
        $oldFiles = $this->mediaPaths($section);

        $section->update(Arr::only($data, self::FIELDS));

        $this->mediaService->deleteMany(array_diff($oldFiles, $this->mediaPaths($section)));

        return $section;
    }

    /**
     * Delete a block and its files.
     */
    public function delete(LandingSection $section): void
    {
        $files = $this->mediaPaths($section);

        $section->delete();

        $this->mediaService->deleteMany($files);
    }

    /**
     * Save a new block order. Only blocks of the same page as the first ID
     * are moved.
     *
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        $first = LandingSection::query()->find($orderedIds[0] ?? null);

        if ($first === null) {
            return;
        }

        SortOrder::apply(
            LandingSection::query()
                ->where('sectionable_type', $first->sectionable_type)
                ->where('sectionable_id', $first->sectionable_id),
            $orderedIds,
        );
    }

    /**
     * Every stored media path inside a block's `data`, following the dot-paths
     * its type declares. Used to delete the files when the block goes away.
     *
     * @return array<int, string>
     */
    public function mediaPaths(LandingSection $section): array
    {
        $paths = [];

        foreach ($section->type->mediaPaths() as $pattern) {
            foreach (Arr::wrap(data_get($section->data ?? [], $pattern)) as $value) {
                if (is_string($value) && $value !== '') {
                    $paths[] = $value;
                }
            }
        }

        return array_values(array_unique($paths));
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
