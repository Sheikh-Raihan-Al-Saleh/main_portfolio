<?php

namespace App\Services;

use App\Models\Founder;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Portfolio projects: public listings, the admin CRUD, gallery and tech stack.
 */
class ProjectService
{
    private const SHOWCASE_CACHE_KEY = 'projects.login-showcase';

    /**
     * Columns a project card needs. Keeps list payloads free of the long
     * description text.
     */
    private const CARD_COLUMNS = [
        'id', 'owner_type', 'owner_id', 'title', 'slug', 'summary', 'cover_image_path',
        'role', 'repo_url', 'live_url', 'is_featured', 'is_published', 'sort_order',
        'started_at', 'completed_at',
    ];

    public function __construct(
        private readonly MediaService $media,
        private readonly TechnologyService $technologies,
        private readonly StudioService $studios,
        private readonly FounderService $founders,
        private readonly LandingPageService $landingPages,
    ) {}

    // ------------------------------------------------------------------
    // Public reads
    // ------------------------------------------------------------------

    /**
     * Published projects of one owner, in display order.
     *
     * @return Collection<int, Project>
     */
    public function publishedFor(Model $owner, ?int $limit = null): Collection
    {
        return $this->cardQuery()
            ->published()
            ->ownedBy($owner)
            ->ordered()
            ->when($limit, fn (Builder $query, int $limit) => $query->limit($limit))
            ->get();
    }

    public function countPublishedFor(Model $owner): int
    {
        return Project::query()->published()->ownedBy($owner)->count();
    }

    /**
     * Every published project (studio and founder) for the /projects archive.
     *
     * @return Collection<int, Project>
     */
    public function publishedArchive(): Collection
    {
        return $this->cardQuery()->published()->ordered()->get();
    }

    /**
     * A published project with everything its detail page shows.
     */
    public function loadForShow(Project $project): Project
    {
        return $project->load([
            'images',
            'technologies:id,name,slug',
        ])->loadExists(['landingPage as has_landing_page' => fn ($query) => $query->published()]);
    }

    /**
     * Up to three other published projects of the same owner.
     *
     * @return Collection<int, Project>
     */
    public function related(Project $project, int $limit = 3): Collection
    {
        return $this->cardQuery()
            ->published()
            ->where('owner_type', $project->owner_type)
            ->where('owner_id', $project->owner_id)
            ->whereKeyNot($project->id)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * A few published covers for the login page's 3D scene. Cached briefly:
     * auth pages are hit by bots and should not query on every request.
     *
     * @return array<int, array{title: string, cover: string}>
     */
    public function loginShowcase(): array
    {
        return Cache::remember(self::SHOWCASE_CACHE_KEY, now()->addMinutes(10), fn (): array => Project::query()
            ->published()
            ->whereNotNull('cover_image_path')
            ->ordered()
            ->limit(6)
            ->get(['id', 'title', 'cover_image_path'])
            ->map(fn (Project $project): array => [
                'title' => $project->title,
                'cover' => (string) $project->cover_image_url,
            ])
            ->all());
    }

    // ------------------------------------------------------------------
    // Admin
    // ------------------------------------------------------------------

    /**
     * @return Collection<int, Project>
     */
    public function adminIndex(?string $search): Collection
    {
        return Project::query()
            ->select(self::CARD_COLUMNS)
            ->with('technologies:id,name')
            ->withCount('images')
            ->withExists('landingPage as has_landing_page')
            ->when(filled($search), fn (Builder $query) => $query->where(
                fn (Builder $inner) => $inner->whereLike('title', "%{$search}%")
                    ->orWhereLike('summary', "%{$search}%"),
            ))
            ->ordered()
            ->get();
    }

    public function loadForEdit(Project $project): Project
    {
        return $project->load(['images', 'technologies:id,name']);
    }

    /**
     * Who a project can belong to, for the admin owner select.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function ownerOptions(): array
    {
        return [
            ['value' => 'studio', 'label' => $this->studios->current()->name.' (studio work)'],
            ['value' => 'founder', 'label' => $this->founders->current()->name.' (personal work)'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data): Project {
            $project = new Project($this->attributes($data));
            $project->owner()->associate($this->owner((string) $data['owner']));
            $project->slug = $this->uniqueSlug($data['slug'] ?? null, $project->title);
            $project->sort_order = (int) Project::query()->max('sort_order') + 1;
            $project->published_at = $project->is_published ? now() : null;
            $project->cover_image_path = isset($data['cover_image'])
                ? $this->media->store($data['cover_image'], 'projects')
                : null;
            $project->save();

            $this->syncTechnologies($project, $data['tech_stack'] ?? []);
            $this->addGalleryImages($project, $data['gallery'] ?? []);

            $this->forgetShowcase();

            return $project;
        });
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data): Project {
            $wasPublished = $project->is_published;

            $project->fill($this->attributes($data));
            $project->owner()->associate($this->owner((string) $data['owner']));
            $project->slug = $this->uniqueSlug($data['slug'] ?? null, $project->title, $project->id);

            if ($project->is_published && ! $wasPublished) {
                $project->published_at = now();
            }

            $project->cover_image_path = $this->media->replace(
                $data['cover_image'] ?? null,
                'projects',
                $project->cover_image_path,
                (bool) ($data['remove_cover_image'] ?? false),
            );
            $project->save();

            $this->syncTechnologies($project, $data['tech_stack'] ?? []);
            $this->removeGalleryImages($project, $data['remove_gallery'] ?? []);
            $this->addGalleryImages($project, $data['gallery'] ?? []);

            $this->forgetShowcase();

            return $project;
        });
    }

    /**
     * Delete a project and everything it owns.
     *
     * Images cascade by foreign key. Landing sections are polymorphic (no
     * foreign key), so the landing page is removed through its service, and
     * files on disk are deleted explicitly.
     */
    public function delete(Project $project): void
    {
        $paths = [
            $project->cover_image_path,
            ...$project->images()->pluck('path'),
        ];

        DB::transaction(function () use ($project): void {
            $this->landingPages->delete($project);
            $project->delete();
        });

        $this->media->deleteMany($paths);
        $this->forgetShowcase();
    }

    /**
     * A URL-safe slug, with a numeric suffix until it is unique.
     */
    public function uniqueSlug(?string $requested, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug(filled($requested) ? (string) $requested : $title) ?: 'project';
        $slug = $base;
        $suffix = 2;

        while (Project::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    // ------------------------------------------------------------------
    // Internals
    // ------------------------------------------------------------------

    /**
     * The base query for any project card list.
     *
     * @return Builder<Project>
     */
    private function cardQuery(): Builder
    {
        return Project::query()
            ->select(self::CARD_COLUMNS)
            ->with('technologies:id,name,slug')
            ->withExists(['landingPage as has_landing_page' => fn ($query) => $query->published()]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return collect($data)->only([
            'title', 'summary', 'description', 'role', 'repo_url', 'live_url',
            'is_featured', 'is_published', 'started_at', 'completed_at',
        ])->all();
    }

    private function owner(string $key): Studio|Founder
    {
        return $key === 'founder' ? $this->founders->current() : $this->studios->current();
    }

    /**
     * @param  array<int, string>  $names
     */
    private function syncTechnologies(Project $project, array $names): void
    {
        $sync = [];

        foreach ($this->technologies->resolveIds($names) as $position => $id) {
            $sync[$id] = ['sort_order' => $position];
        }

        $project->technologies()->sync($sync);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function addGalleryImages(Project $project, array $files): void
    {
        if ($files === []) {
            return;
        }

        $position = (int) $project->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            $project->images()->create([
                'path' => $this->media->store($file, 'projects/gallery'),
                'alt' => $project->title,
                'sort_order' => $position++,
            ]);
        }
    }

    /**
     * Remove the ticked gallery images. Scoped to this project, so a forged
     * request cannot delete another project's images.
     *
     * @param  array<int, int>  $imageIds
     */
    private function removeGalleryImages(Project $project, array $imageIds): void
    {
        if ($imageIds === []) {
            return;
        }

        $images = $project->images()->whereKey($imageIds)->get(['id', 'project_id', 'path']);

        $images->each(fn (ProjectImage $image) => $image->delete());

        $this->media->deleteMany($images->pluck('path'));
    }

    private function forgetShowcase(): void
    {
        Cache::forget(self::SHOWCASE_CACHE_KEY);
    }
}
