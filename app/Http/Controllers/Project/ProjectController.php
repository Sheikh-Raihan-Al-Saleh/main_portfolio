<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\ReorderRequest;
use App\Http\Resources\Project\ProjectFormResource;
use App\Models\Founder;
use App\Models\Project;
use App\Models\Studio;
use App\Models\Technology;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: projects. Create, edit, delete, reorder, with the gallery and
 * tech stack.
 */
class ProjectController extends Controller
{
    public function __construct(private ProjectService $projects) {}

    /**
     * Show the list of projects, optionally searched by title or summary.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $query = Project::select(Project::CARD_COLUMNS)
            ->with('technologies:id,name')
            ->withCount('images')
            ->withExists('landingPage as has_landing_page')
            ->ordered();

        if ($search !== '') {
            $query->whereAny(['title', 'summary'], 'like', "%{$search}%");
        }

        $projects = $query->get();

        return Inertia::render('project/ProjectIndex', [
            'projects' => ProjectFormResource::collection($projects)->resolve(),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    /**
     * Show the form to create a project.
     */
    public function create(): Response
    {
        return Inertia::render('project/CreateProject', [
            'owners' => $this->ownerOptions(),
            'technologyOptions' => $this->technologyNames(),
        ]);
    }

    /**
     * Save a new project with its cover, gallery and tech stack.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->projects->create($request->validated());

        $this->toast(__('Project created.'));

        return to_route('projectIndex');
    }

    /**
     * Show the form to edit a project, with its gallery and stack.
     */
    public function edit(Project $project): Response
    {
        $project->load(['images', 'technologies:id,name']);

        return Inertia::render('project/EditProject', [
            'project' => ProjectFormResource::make($project)->resolve(),
            'owners' => $this->ownerOptions(),
            'technologyOptions' => $this->technologyNames(),
        ]);
    }

    /**
     * Save the changes: details, cover, stack, and gallery additions and
     * removals.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->projects->update($project, $request->validated());

        $this->toast(__('Project updated.'));

        return to_route('projectIndex');
    }

    /**
     * Delete a project with its landing page, blocks and files.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->projects->delete($project);

        $this->toast(__('Project deleted.'), 'error');

        return back();
    }

    /**
     * Save the new display order of the projects.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->saveSortOrder(Project::query(), $request->validated('ids'));

        // A query update fires no model events, so clear the cache here.
        Project::forgetShowcase();

        return back();
    }

    /**
     * Who a project can belong to, for the owner select.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function ownerOptions(): array
    {
        return [
            ['value' => 'studio', 'label' => Studio::current()->name.' (studio work)'],
            ['value' => 'founder', 'label' => Founder::current()->name.' (personal work)'],
        ];
    }

    /**
     * Technology names, for the tech stack suggestions.
     *
     * @return array<int, string>
     */
    private function technologyNames(): array
    {
        return Technology::orderBy('name')->pluck('name')->all();
    }
}
