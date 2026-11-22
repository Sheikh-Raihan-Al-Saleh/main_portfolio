<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesMediaUploads;
use App\Enums\ProjectContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use HandlesMediaUploads;

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        // Escape LIKE wildcards so a search for "100%" matches literally. '!'
        // is the escape character because MySQL and SQLite disagree on '\'.
        $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';

        return inertia('admin/projects/Index', [
            'projects' => Project::query()
                ->when($search !== '', fn ($query) => $query->where(
                    fn ($q) => $q->whereRaw("title like ? escape '!'", [$like])
                        ->orWhereRaw("summary like ? escape '!'", [$like]),
                ))
                ->ordered()
                ->get(),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/projects/Create', [
            'contexts' => ProjectContext::options(),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = new Project($request->projectAttributes());

        $project->cover_image_path = $this->storeMedia(
            $request->file('cover_image'), 'projects',
        );
        $project->gallery = $this->storeGallery($request, []);
        $project->sort_order = (int) Project::query()->max('sort_order') + 1;
        $project->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project created.')]);

        return to_route('admin.projects.index');
    }

    public function edit(Project $project): Response
    {
        return inertia('admin/projects/Edit', [
            'project' => $project,
            // Raw paths alongside URLs, so the form can tick individual images
            // for removal. gallery_urls drops missing files, so it cannot be
            // matched back to paths by index.
            'galleryItems' => collect($project->gallery ?? [])
                ->map(fn (string $path): array => [
                    'path' => $path,
                    'url' => Storage::disk('uploads')->url($path),
                ])
                ->values(),
            'contexts' => ProjectContext::options(),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->fill($request->projectAttributes());

        if ($request->boolean('remove_cover_image')) {
            $this->deleteMedia($project->cover_image_path);
            $project->cover_image_path = null;
        }

        $project->cover_image_path = $this->storeMedia(
            $request->file('cover_image'), 'projects', $project->cover_image_path,
        );
        $project->gallery = $this->storeGallery(
            $request,
            $this->removeFromGallery($project->gallery ?? [], $request->input('remove_gallery', [])),
        );
        $project->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project updated.')]);

        return to_route('admin.projects.index');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $landingPage = $project->landingPage()->with('sections')->first();

        $this->deleteMedia($project->cover_image_path);
        $this->deleteMediaMany($project->gallery);

        // The landing page and its sections cascade in the database, but their
        // files on disk would otherwise be orphaned.
        if ($landingPage instanceof ProjectLandingPage) {
            $this->deleteMediaMany($landingPage->mediaPaths());
        }

        $project->delete();

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Project deleted.')]);

        return back();
    }

    /**
     * Persist a new drag-and-drop ordering for the project list.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:projects,id'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['ids'] as $position => $id) {
                Project::query()->whereKey($id)->update(['sort_order' => $position]);
            }
        });

        return back();
    }

    /**
     * Drop the ticked images from the gallery and delete their files. Only
     * paths already in this gallery are honoured, so a forged request cannot
     * delete arbitrary files from the uploads disk.
     *
     * @param  array<int, string>  $gallery
     * @param  array<int, mixed>  $remove
     * @return array<int, string>
     */
    private function removeFromGallery(array $gallery, array $remove): array
    {
        $remove = array_values(array_intersect($gallery, array_filter($remove, is_string(...))));

        $this->deleteMediaMany($remove);

        return array_values(array_diff($gallery, $remove));
    }

    /**
     * Append any newly uploaded gallery images to the existing set.
     *
     * @param  array<int, string>  $existing
     * @return array<int, string>
     */
    private function storeGallery(ProjectRequest $request, array $existing): array
    {
        foreach ($request->file('gallery') ?? [] as $image) {
            $existing[] = $this->putMedia($image, 'projects/gallery');
        }

        return array_values($existing);
    }
}
