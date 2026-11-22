<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Http\Resources\LandingPage\LandingSectionResource;
use App\Http\Resources\Project\ProjectCardResource;
use App\Services\Client\ClientService;
use App\Services\Founder\FounderService;
use App\Services\LandingPage\LandingSectionService;
use App\Services\Project\ProjectService;
use App\Services\Studio\StudioService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public studio pages: the home page (/) and the About page (/about).
 */
class StudioPageController extends Controller
{
    public function __construct(
        private readonly StudioService $studioService,
        private readonly FounderService $founderService,
        private readonly ProjectService $projectService,
        private readonly ClientService $clientService,
        private readonly LandingSectionService $sectionService,
    ) {}

    public function home(): Response
    {
        $studio = $this->studioService->current();
        $founder = $this->founderService->current();
        $clients = $this->clientService->visible();
        $totalProjects = $this->projectService->countPublishedFor($studio);

        return Inertia::render('public/home/Index', [
            'content' => $studio->studio_content,
            'projects' => ProjectCardResource::collection($this->projectService->publishedFor($studio, 6))->resolve(),
            'totalProjects' => $totalProjects,
            'founderProjects' => ProjectCardResource::collection($this->projectService->publishedFor($founder, 3))->resolve(),
            'totalFounderProjects' => $this->projectService->countPublishedFor($founder),
            'clients' => $clients,
            'sections' => LandingSectionResource::collection($this->sectionService->visibleFor($studio))->resolve(),
            'stats' => $this->studioService->stats($totalProjects, $clients->count()),
        ]);
    }

    public function about(): Response
    {
        $studio = $this->studioService->current();
        $clients = $this->clientService->visible();

        return Inertia::render('public/about/Index', [
            'clients' => $clients,
            'stats' => $this->studioService->stats($this->projectService->countPublishedFor($studio), $clients->count()),
        ]);
    }
}
