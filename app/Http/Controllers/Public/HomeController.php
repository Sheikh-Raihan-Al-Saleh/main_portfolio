<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\LandingSectionResource;
use App\Http\Resources\ProjectCardResource;
use App\Services\ClientService;
use App\Services\FounderService;
use App\Services\LandingSectionService;
use App\Services\ProjectService;
use App\Services\StudioService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The studio home page (/).
 */
class HomeController extends Controller
{
    public function __construct(
        private readonly StudioService $studios,
        private readonly FounderService $founders,
        private readonly ProjectService $projects,
        private readonly ClientService $clients,
        private readonly LandingSectionService $sections,
    ) {}

    public function __invoke(): Response
    {
        $studio = $this->studios->current();
        $founder = $this->founders->current();
        $clients = $this->clients->visible();
        $totalProjects = $this->projects->countPublishedFor($studio);

        return Inertia::render('public/Home', [
            'content' => $studio->studio_content,
            'projects' => ProjectCardResource::collection($this->projects->publishedFor($studio, 6))->resolve(),
            'totalProjects' => $totalProjects,
            'founderProjects' => ProjectCardResource::collection($this->projects->publishedFor($founder, 3))->resolve(),
            'totalFounderProjects' => $this->projects->countPublishedFor($founder),
            'clients' => $clients,
            'sections' => LandingSectionResource::collection($this->sections->visibleFor($studio))->resolve(),
            'stats' => [
                'projects' => $totalProjects ?: null,
                'clients' => $clients->count() ?: null,
                'yearsInBusiness' => $this->studios->yearsInBusiness($studio),
            ],
        ]);
    }
}
