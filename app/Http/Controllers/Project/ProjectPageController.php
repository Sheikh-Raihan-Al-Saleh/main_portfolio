<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\ProjectCardResource;
use App\Http\Resources\Project\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public: the work archive (/projects) and project pages (/projects/{slug}).
 */
class ProjectPageController extends Controller
{
    /**
     * Every published project. Filtering happens in the browser; the query
     * string only sets the starting filter.
     */
    public function index(Request $request): Response
    {
        $owner = $request->query('owner');

        $projects = Project::forCards()->published()->ordered()->get();

        return Inertia::render('project/ProjectArchive', [
            'projects' => ProjectCardResource::collection($projects)->resolve(),
            'filters' => [
                'tech' => $request->string('tech')->trim()->value() ?: null,
                'owner' => in_array($owner, ['studio', 'founder'], true) ? $owner : null,
            ],
        ]);
    }

    /**
     * Show a published project with up to three related ones. Drafts return 404.
     */
    public function show(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        $project
            ->load(['images', 'technologies:id,name,slug'])
            ->loadExists('publishedLandingPage as has_landing_page');

        $related = Project::forCards()
            ->published()
            ->where('owner_type', $project->owner_type)
            ->where('owner_id', $project->owner_id)
            ->whereKeyNot($project->id)
            ->ordered()
            ->limit(3)
            ->get();

        return Inertia::render('project/ShowProject', [
            'project' => ProjectResource::make($project)->resolve(),
            'related' => ProjectCardResource::collection($related)->resolve(),
        ]);
    }
}
