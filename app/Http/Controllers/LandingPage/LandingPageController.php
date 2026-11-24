<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use App\Http\Resources\LandingPage\LandingPageResource;
use App\Http\Resources\LandingPage\LandingSectionResource;
use App\Models\Project;
use App\Services\FileService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public: a project's case-study page (/p/{slug}).
 */
class LandingPageController extends Controller
{
    /**
     * Show the published landing page of a published project. Drafts return
     * 404, so their existence is not disclosed.
     */
    public function show(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        $landingPage = $project->landingPage()->published()->with('heroMedia')->first();

        abort_if($landingPage === null, 404);

        $sections = $landingPage->sections()->visible()->ordered()->get();

        return Inertia::render('landingpage/ShowLandingPage', [
            'project' => [
                ...$project->only(['id', 'title', 'slug', 'summary', 'role', 'owner_type', 'live_url', 'repo_url']),
                'cover_image_url' => FileService::url($project->cover_image_path),
                'started_at' => $project->started_at?->toDateString(),
                'completed_at' => $project->completed_at?->toDateString(),
            ],
            'landingPage' => LandingPageResource::make($landingPage)->resolve(),
            'sections' => LandingSectionResource::collection($sections)->resolve(),
        ]);
    }
}
