<?php

namespace App\Services;

use App\Models\LandingSection;
use App\Models\Project;
use App\Models\Studio;
use Illuminate\Support\Arr;

/**
 * Creates, updates and deletes the content blocks of the studio home page
 * and of project landing pages, with the files inside their `data`.
 */
class LandingSectionService
{
    /** Block fields saved to the table (the type is set once, on create). */
    private const FIELDS = ['eyebrow', 'heading', 'subheading', 'body', 'data', 'is_visible'];

    public function __construct(private FileService $files) {}

    /**
     * Add a block at the end of its page: the studio home page, or a
     * project's landing page (created on demand, so blocks can be added
     * before the page itself is saved).
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function create(array $data): LandingSection
    {
        $page = $data['owner'] === 'studio'
            ? Studio::current()
            : Project::findOrFail((int) $data['project_id'])->landingPage()->firstOrCreate([]);

        $section = new LandingSection(Arr::only($data, [...self::FIELDS, 'type']));
        $section->sort_order = (int) $page->sections()->max('sort_order') + 1;
        $page->sections()->save($section);

        return $section;
    }

    /**
     * Save a block, deleting any file the edit dropped from it.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function update(LandingSection $section, array $data): void
    {
        $oldFiles = $section->mediaPaths();

        $section->update(Arr::only($data, self::FIELDS));

        $this->files->deleteMany(array_diff($oldFiles, $section->mediaPaths()));
    }

    /**
     * Delete a block and its files.
     */
    public function delete(LandingSection $section): void
    {
        $files = $section->mediaPaths();

        $section->delete();

        $this->files->deleteMany($files);
    }
}
