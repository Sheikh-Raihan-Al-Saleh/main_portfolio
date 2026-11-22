<?php

namespace App\Http\Controllers\LandingPage;

use App\Http\Controllers\Controller;
use App\Http\Resources\LandingPage\LandingPageResource;
use App\Http\Resources\LandingPage\LandingSectionResource;
use App\Models\Project;
use App\Services\LandingPage\LandingPageService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public: a project's case-study page (/p/{slug}).
 */
class LandingPageController extends Controller
{
    public function __construct(
        private readonly LandingPageService $landingPageService,
    ) {}

    /**
     * Drafts return 404, so their existence is not disclosed.
     */
    public function show(Project $project): Response
    {
        $landingPage = $this->landingPageService->findPublished($project);

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
