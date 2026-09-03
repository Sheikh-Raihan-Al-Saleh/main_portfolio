<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesMediaUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use HandlesMediaUploads;

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return inertia('admin/projects/Index', [
            'projects' => Project::query()
                ->when($search !== '', fn ($query) => $query->where(
                    fn ($q) => $q->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%"),
                ))
                ->ordered()
                ->get(),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/projects/Create');
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
        return inertia('admin/projects/Edit', ['project' => $project]);
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
        $project->gallery = $this->storeGallery($request, $project->gallery ?? []);
        $project->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Project updated.')]);

        return to_route('admin.projects.index');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->deleteMedia($project->cover_image_path);
        $this->deleteMediaMany($project->gallery);
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

        foreach ($validated['ids'] as $position => $id) {
            Project::query()->whereKey($id)->update(['sort_order' => $position]);
        }

        return back();
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
