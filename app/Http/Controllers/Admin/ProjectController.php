<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use App\Services\TechnologyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projects,
        private readonly TechnologyService $technologies,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('admin/projects/Index', [
            'projects' => $this->projects->adminIndex($search),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/projects/Create', $this->formOptions());
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $this->projects->create($request->validated());

        $this->toast(__('Project created.'));

        return to_route('admin.projects.index');
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('admin/projects/Edit', [
            'project' => $this->projects->loadForEdit($project),
            ...$this->formOptions(),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projects->update($project, $request->validated());

        $this->toast(__('Project updated.'));

        return to_route('admin.projects.index');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->projects->delete($project);

        $this->toast(__('Project deleted.'), 'error');

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'owners' => $this->projects->ownerOptions(),
            'technologyOptions' => $this->technologies->all()->pluck('name'),
        ];
    }
}
