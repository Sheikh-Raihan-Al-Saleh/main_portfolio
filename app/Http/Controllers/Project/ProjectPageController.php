<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\ProjectCardResource;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use App\Services\Project\ProjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public: the work archive (/projects) and project pages (/projects/{slug}).
 */
class ProjectPageController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
    ) {}

    /**
     * Every published project. Filtering happens in the browser; the query
     * string only sets the starting filter.
     */
    public function index(Request $request): Response
    {
        $owner = $request->query('owner');

        return Inertia::render('public/projects/Index', [
            'projects' => ProjectCardResource::collection($this->projectService->publishedArchive())->resolve(),
            'filters' => [
                'tech' => $request->string('tech')->trim()->value() ?: null,
                'owner' => in_array($owner, ['studio', 'founder'], true) ? $owner : null,
            ],
        ]);
    }

    /**
     * Show a published project. Drafts return 404.
     */
    public function show(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        return Inertia::render('public/projects/Show', [
            'project' => ProjectResource::make($this->projectService->forShow($project))->resolve(),
            'related' => ProjectCardResource::collection($this->projectService->related($project))->resolve(),
        ]);
    }
}
