<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectCardResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The public work archive (/projects) and project detail pages.
 */
class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projects,
    ) {}

    /**
     * Every published project in one payload. Filtering by owner and
     * technology happens client-side, so switching filters is instant; the
     * query string only seeds the initial selection.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('public/Projects', [
            'projects' => ProjectCardResource::collection($this->projects->publishedArchive())->resolve(),
            'filters' => [
                'tech' => $request->string('tech')->trim()->value() ?: null,
                'owner' => in_array($request->query('owner'), ['studio', 'founder'], true)
                    ? $request->query('owner')
                    : null,
            ],
        ]);
    }

    public function show(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        return Inertia::render('public/ProjectShow', [
            'project' => ProjectResource::make($this->projects->loadForShow($project))->resolve(),
            'related' => ProjectCardResource::collection($this->projects->related($project))->resolve(),
        ]);
    }
}
