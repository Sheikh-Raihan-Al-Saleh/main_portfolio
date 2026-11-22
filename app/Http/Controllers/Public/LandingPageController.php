<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\LandingPageResource;
use App\Http\Resources\LandingSectionResource;
use App\Models\Project;
use App\Services\LandingPageService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A project's marketing case-study page (/p/{slug}).
 */
class LandingPageController extends Controller
{
    public function __construct(
        private readonly LandingPageService $landingPages,
    ) {}

    /**
     * Drafts (an unpublished project or page) return 404, so their existence
     * is not disclosed.
     */
    public function __invoke(Project $project): Response
    {
        $landingPage = $this->landingPages->findPublished($project);

        abort_if($landingPage === null, 404);

        return Inertia::render('public/landing/Show', [
            'project' => [
                ...$project->only(['id', 'title', 'slug', 'summary', 'role', 'owner_type', 'live_url', 'repo_url', 'cover_image_url']),
                'started_at' => $project->started_at?->toDateString(),
                'completed_at' => $project->completed_at?->toDateString(),
            ],
            'landingPage' => LandingPageResource::make($landingPage)->resolve(),
            'sections' => LandingSectionResource::collection($landingPage->sections)->resolve(),
        ]);
    }
}
