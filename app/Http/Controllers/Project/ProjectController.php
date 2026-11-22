<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\ReorderRequest;
use App\Models\Project;
use App\Services\Project\ProjectService;
use App\Services\Technology\TechnologyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: projects.
 */
class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly TechnologyService $technologyService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('admin/projects/Index', [
            'projects' => $this->projectService->list($search),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/projects/Create', [
            'owners' => $this->projectService->ownerOptions(),
            'technologyOptions' => $this->technologyService->names(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->projectService->create($request->validated());
        $this->toast(__('Project created.'));

        return to_route('admin.projects.index');
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('admin/projects/Edit', [
            'project' => $this->projectService->forEdit($project),
            'owners' => $this->projectService->ownerOptions(),
            'technologyOptions' => $this->technologyService->names(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->update($project, $request->validated());
        $this->toast(__('Project updated.'));

        return to_route('admin.projects.index');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->projectService->delete($project);
        $this->toast(__('Project deleted.'), 'error');

        return back();
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->projectService->reorder($request->validated('ids'));

        return back();
    }
}
