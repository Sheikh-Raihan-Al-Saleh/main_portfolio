<?php

namespace App\Services;

use App\Models\Founder;
use App\Models\Project;
use App\Models\Studio;
use App\Models\Technology;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates, updates and deletes projects with their cover, gallery, tech
 * stack and landing page.
 */
class ProjectService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['title', 'summary', 'description', 'role', 'repo_url', 'live_url', 'is_featured', 'is_published', 'started_at', 'completed_at'];

    public function __construct(
        private FileService $files,
        private LandingPageService $landingPages,
    ) {}

    /**
     * Save a new project at the end of the list, with its cover, gallery and
     * tech stack.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data): Project {
            $project = new Project(Arr::only($data, self::FIELDS));
            $project->owner()->associate($this->owner($data['owner']));
            $project->slug = $this->uniqueSlug($data['slug'] ?? $data['title']);
            $project->sort_order = (int) Project::max('sort_order') + 1;
            $project->published_at = $project->is_published ? now() : null;
            $project->cover_image_path = isset($data['cover_image']) ? $this->files->store($data['cover_image'], 'projects') : null;
            $project->save();

            $this->syncTechnologies($project, $data['tech_stack'] ?? []);
            $this->addGalleryImages($project, $data['gallery'] ?? []);

            return $project;
        });
    }

    /**
     * Save the changes: details, cover, tech stack, and gallery additions and
     * removals.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function update(Project $project, array $data): void
    {
        DB::transaction(function () use ($project, $data): void {
            $wasPublished = $project->is_published;

            $project->fill(Arr::only($data, self::FIELDS));
            $project->owner()->associate($this->owner($data['owner']));
            $project->slug = $this->uniqueSlug($data['slug'] ?? $data['title'], $project->id);

            if ($project->is_published && ! $wasPublished) {
                $project->published_at = now();
            }

            $project->cover_image_path = $this->files->replace(
                $data['cover_image'] ?? null,
                'projects',
                $project->cover_image_path,
                (bool) ($data['remove_cover_image'] ?? false),
            );
            $project->save();

            $this->syncTechnologies($project, $data['tech_stack'] ?? []);
            $this->addGalleryImages($project, $data['gallery'] ?? []);
            $this->removeGalleryImages($project, $data['remove_gallery'] ?? []);
        });
    }

    /**
     * Delete a project with its landing page, gallery and files.
     */
    public function delete(Project $project): void
    {
        $files = [$project->cover_image_path, ...$project->images()->pluck('path')];
        $landingPage = $project->landingPage()->with(['heroMedia', 'sections'])->first();

        DB::transaction(function () use ($project, $landingPage): void {
            // Landing blocks are polymorphic (no foreign key), so the landing
            // page and its blocks go before the project row.
            if ($landingPage !== null) {
                $this->landingPages->delete($landingPage);
            }

            $project->delete();
        });

        $this->files->deleteMany($files);
    }

    /**
     * A URL-safe slug with a numeric suffix until it is unique.
     */
    public function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::slug($text) ?: 'project';
        $slug = $base;
        $suffix = 2;

        while ($this->slugIsTaken($slug, $ignoreId)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * Whether another project already uses this slug.
     */
    private function slugIsTaken(string $slug, ?int $ignoreId): bool
    {
        $query = Project::where('slug', $slug);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }

    /**
     * Save the tech stack from a list of names, keeping their order. Names
     * match by slug ("Vue.js" and "vue.js" are the same technology), and new
     * names are added to the shared technology list.
     *
     * @param  array<int, string>  $names
     */
    public function syncTechnologies(Project $project, array $names): void
    {
        $technologyIds = [];

        foreach ($names as $name) {
            $name = trim($name);
            $slug = Str::slug($name);

            if ($slug !== '') {
                $technologyIds[] = Technology::firstOrCreate(['slug' => $slug], ['name' => $name])->id;
            }
        }

        $pivotData = [];

        foreach (array_values(array_unique($technologyIds)) as $position => $technologyId) {
            $pivotData[$technologyId] = ['sort_order' => $position];
        }

        $project->technologies()->sync($pivotData);
    }

    /**
     * The owner record for the form value: "founder" or "studio".
     */
    private function owner(string $key): Studio|Founder
    {
        return $key === 'founder' ? Founder::current() : Studio::current();
    }

    /**
     * Store uploaded gallery images at the end of the gallery.
     *
     * @param  array<int, UploadedFile>  $files
     */
    private function addGalleryImages(Project $project, array $files): void
    {
        $position = (int) $project->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            $project->images()->create([
                'path' => $this->files->store($file, 'projects/gallery'),
                'alt' => $project->title,
                'sort_order' => $position++,
            ]);
        }
    }

    /**
     * Delete gallery images by ID. Only this project's images can be removed,
     * whatever IDs are sent.
     *
     * @param  array<int, int>  $imageIds
     */
    private function removeGalleryImages(Project $project, array $imageIds): void
    {
        $images = $project->images()->whereKey($imageIds)->get();

        $project->images()->whereKey($images->modelKeys())->delete();
        $this->files->deleteMany($images->pluck('path'));
    }
}
