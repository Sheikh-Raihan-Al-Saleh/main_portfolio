<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Services\Founder\FounderService;
use App\Services\LandingPage\LandingPageService;
use App\Services\Media\MediaService;
use App\Services\Studio\StudioService;
use App\Services\Technology\TechnologyService;
use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Project module: public listings, the admin CRUD, gallery images and the
 * tech stack.
 */
class ProjectService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['title', 'summary', 'description', 'role', 'repo_url', 'live_url', 'is_featured', 'is_published', 'started_at', 'completed_at'];

    private const SHOWCASE_CACHE_KEY = 'projects.login-showcase';

    /** Columns a project card needs (no long description). */
    private const CARD_COLUMNS = [
        'id', 'owner_type', 'owner_id', 'title', 'slug', 'summary', 'cover_image_path',
        'role', 'repo_url', 'live_url', 'is_featured', 'is_published', 'sort_order',
        'started_at', 'completed_at',
    ];

    public function __construct(
        private readonly MediaService $mediaService,
        private readonly TechnologyService $technologyService,
        private readonly StudioService $studioService,
        private readonly FounderService $founderService,
        private readonly LandingPageService $landingPageService,
    ) {}

    // ── Public pages ──────────────────────────────────────────────

    /**
     * Published projects of one owner (studio or founder), in order.
     *
     * @return Collection<int, Project>
     */
    public function publishedFor(Model $owner, ?int $limit = null): Collection
    {
        return $this->inDisplayOrder($this->cards()->where('is_published', true)->whereMorphedTo('owner', $owner))
            ->when($limit, fn (Builder $query) => $query->limit($limit))
            ->get();
    }

    /**
     * How many published projects one owner (studio or founder) has.
     */
    public function countPublishedFor(Model $owner): int
    {
        return Project::query()->where('is_published', true)->whereMorphedTo('owner', $owner)->count();
    }

    /**
     * Every published project, for the /projects archive.
     *
     * @return Collection<int, Project>
     */
    public function publishedArchive(): Collection
    {
        return $this->inDisplayOrder($this->cards()->where('is_published', true))->get();
    }

    /**
     * A project with everything its detail page shows.
     */
    public function forShow(Project $project): Project
    {
        return $project
            ->load(['images', 'technologies:id,name,slug'])
            ->loadExists(['landingPage as has_landing_page' => fn (Builder $query) => $query->where('is_published', true)]);
    }

    /**
     * Up to three other published projects of the same owner.
     *
     * @return Collection<int, Project>
     */
    public function related(Project $project): Collection
    {
        $relatedQuery = $this->cards()
            ->where('is_published', true)
            ->where('owner_type', $project->owner_type)
            ->where('owner_id', $project->owner_id)
            ->whereKeyNot($project->id);

        return $this->inDisplayOrder($relatedQuery)->limit(3)->get();
    }

    /**
     * Published covers for the sign-in pages, in display order
     * (cached for 10 minutes).
     *
     * @return array<int, array{title: string, cover: string}>
     */
    public function loginShowcase(): array
    {
        return Cache::remember(self::SHOWCASE_CACHE_KEY, now()->addMinutes(10), fn (): array => $this->inDisplayOrder(Project::query()->where('is_published', true)->whereNotNull('cover_image_path'))
            ->limit(6)
            ->get(['id', 'title', 'cover_image_path'])
            ->map(fn (Project $project): array => ['title' => $project->title, 'cover' => (string) MediaService::url($project->cover_image_path)])
            ->all());
    }

    // ── Admin ─────────────────────────────────────────────────────

    /**
     * All projects for the admin list, optionally searched by title or summary.
     *
     * @return Collection<int, Project>
     */
    public function list(?string $search = null): Collection
    {
        $projectQuery = Project::query()
            ->select(self::CARD_COLUMNS)
            ->with('technologies:id,name')
            ->withCount('images')
            ->withExists('landingPage as has_landing_page')
            ->when($search, fn (Builder $query) => $query->where(
                fn (Builder $searchQuery) => $searchQuery->whereLike('title', "%{$search}%")->orWhereLike('summary', "%{$search}%"),
            ));

        return $this->inDisplayOrder($projectQuery)->get();
    }

    /**
     * A project with its gallery and stack, for the edit form.
     */
    public function forEdit(Project $project): Project
    {
        return $project->load(['images', 'technologies:id,name']);
    }

    /**
     * Who a project can belong to, for the owner select.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function ownerOptions(): array
    {
        return [
            ['value' => 'studio', 'label' => $this->studioService->current()->name.' (studio work)'],
            ['value' => 'founder', 'label' => $this->founderService->current()->name.' (personal work)'],
        ];
    }

    /**
     * Create a project with its cover, gallery and tech stack.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Project
    {
        $project = DB::transaction(function () use ($data): Project {
            $project = new Project(Arr::only($data, self::FIELDS));
            $project->owner()->associate($this->owner($data['owner']));
            $project->slug = $this->uniqueSlug($data['slug'] ?? $data['title']);
            $project->sort_order = (int) Project::query()->max('sort_order') + 1;
            $project->published_at = $project->is_published ? now() : null;
            $project->cover_image_path = isset($data['cover_image']) ? $this->mediaService->store($data['cover_image'], 'projects') : null;
            $project->save();

            $this->saveTechnologies($project, $data['tech_stack'] ?? []);
            $this->addImages($project, $data['gallery'] ?? []);

            return $project;
        });

        Cache::forget(self::SHOWCASE_CACHE_KEY);

        return $project;
    }

    /**
     * Update a project: details, cover, stack, and gallery additions/removals.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Project $project, array $data): Project
    {
        DB::transaction(function () use ($project, $data): void {
            $wasPublished = $project->is_published;

            $project->fill(Arr::only($data, self::FIELDS));
            $project->owner()->associate($this->owner($data['owner']));
            $project->slug = $this->uniqueSlug($data['slug'] ?? $data['title'], $project->id);
            $project->published_at = $project->is_published && ! $wasPublished ? now() : $project->published_at;
            $project->cover_image_path = $this->mediaService->replace(
                $data['cover_image'] ?? null,
                'projects',
                $project->cover_image_path,
                (bool) ($data['remove_cover_image'] ?? false),
            );
            $project->save();

            $this->saveTechnologies($project, $data['tech_stack'] ?? []);
            $this->addImages($project, $data['gallery'] ?? []);

            // Only this project's images can be removed, whatever IDs are sent.
            $removed = $project->images()->whereKey($data['remove_gallery'] ?? [])->get();
            $project->images()->whereKey($removed->modelKeys())->delete();
            $this->mediaService->deleteMany($removed->pluck('path'));
        });

        Cache::forget(self::SHOWCASE_CACHE_KEY);

        return $project;
    }

    /**
     * Delete a project with its landing page, blocks and files.
     */
    public function delete(Project $project): void
    {
        $files = [$project->cover_image_path, ...$project->images()->pluck('path')];

        DB::transaction(function () use ($project): void {
            // Landing blocks are polymorphic (no foreign key), so the landing
            // page service removes them before the project row goes.
            $this->landingPageService->delete($project);
            $project->delete();
        });

        $this->mediaService->deleteMany($files);
        Cache::forget(self::SHOWCASE_CACHE_KEY);
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        SortOrder::apply(Project::query(), $orderedIds);
    }

    /**
     * A URL-safe slug with a numeric suffix until it is unique.
     */
    public function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::slug($text) ?: 'project';
        $slug = $base;
        $suffix = 2;

        while (Project::query()->where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * The base query of every project card list.
     *
     * @return Builder<Project>
     */
    private function cards(): Builder
    {
        return Project::query()
            ->select(self::CARD_COLUMNS)
            ->with('technologies:id,name,slug')
            ->withExists(['landingPage as has_landing_page' => fn (Builder $query) => $query->where('is_published', true)]);
    }

    /**
     * Display order of every project list: the manual sort order first,
     * then the newest project first when two share a position.
     *
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function inDisplayOrder(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    /**
     * The owner record for the form value: "founder" or "studio".
     */
    private function owner(string $key): Model
    {
        return $key === 'founder' ? $this->founderService->current() : $this->studioService->current();
    }

    /**
     * @param  array<int, string>  $names
     */
    private function saveTechnologies(Project $project, array $names): void
    {
        $sync = [];

        foreach ($this->technologyService->idsFor($names) as $position => $id) {
            $sync[$id] = ['sort_order' => $position];
        }

        $project->technologies()->sync($sync);
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function addImages(Project $project, array $files): void
    {
        $position = (int) $project->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            $project->images()->create([
                'path' => $this->mediaService->store($file, 'projects/gallery'),
                'alt' => $project->title,
                'sort_order' => $position++,
            ]);
        }
    }
}
