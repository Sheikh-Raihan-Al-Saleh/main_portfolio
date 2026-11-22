<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\ProjectCardResource;
use App\Services\Founder\FounderService;
use App\Services\Project\ProjectService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public founder pages: the portfolio (/founder) and the resume (/resume).
 */
class FounderPageController extends Controller
{
    public function __construct(
        private readonly FounderService $founderService,
        private readonly ProjectService $projectService,
    ) {}

    public function show(): Response
    {
        $founder = $this->founderService->withPortfolio();
        $projects = $this->projectService->publishedFor($founder);

        return Inertia::render('public/founder/Index', [
            'content' => $founder->founder_content,
            'skillGroups' => $founder->skillCategories,
            'experiences' => $founder->experiences,
            'educations' => $founder->educations,
            'projects' => ProjectCardResource::collection($projects)->resolve(),
            'stats' => $this->founderService->stats($founder, $projects->count()),
        ]);
    }

    public function resume(): StreamedResponse
    {
        $response = $this->founderService->resumeResponse();

        abort_if($response === null, 404);

        return $response;
    }
}
