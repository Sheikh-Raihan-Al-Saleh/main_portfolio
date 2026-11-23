<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\ReorderRequest;
use App\Http\Resources\Project\ProjectFormResource;
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

    /**
     * Show the list of projects.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('admin/projects/Index', [
            'projects' => ProjectFormResource::collection($this->projectService->list($search))->resolve(),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    /**
     * Show the form to create a project.
     */
    public function create(): Response
    {
        return Inertia::render('admin/projects/Create', [
            'owners' => $this->projectService->ownerOptions(),
            'technologyOptions' => $this->technologyService->names(),
        ]);
    }

    /**
     * Validate and save a new project. The service does the work.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->projectService->create($request->validated());
        $this->toast(__('Project created.'));

        return to_route('admin.projects.index');
    }

    /**
     * Show the form to edit a project.
     */
    public function edit(Project $project): Response
    {
        return Inertia::render('admin/projects/Edit', [
            'project' => ProjectFormResource::make($this->projectService->forEdit($project))->resolve(),
            'owners' => $this->projectService->ownerOptions(),
            'technologyOptions' => $this->technologyService->names(),
        ]);
    }

    /**
     * Validate and save the changes to a project. The service does the work.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projectService->update($project, $request->validated());
        $this->toast(__('Project updated.'));

        return to_route('admin.projects.index');
    }

    /**
     * Delete a project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->projectService->delete($project);
        $this->toast(__('Project deleted.'), 'error');

        return back();
    }

    /**
     * Save the new display order of the projects.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->projectService->reorder($request->validated('ids'));

        return back();
    }
}
