<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ClientService;
use App\Services\ProjectService;
use App\Services\StudioService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The studio About page (/about). The studio and founder records themselves
 * are shared on every page by HandleInertiaRequests.
 */
class AboutController extends Controller
{
    public function __construct(
        private readonly StudioService $studios,
        private readonly ProjectService $projects,
        private readonly ClientService $clients,
    ) {}

    public function __invoke(): Response
    {
        $studio = $this->studios->current();
        $clients = $this->clients->visible();

        return Inertia::render('public/About', [
            'clients' => $clients,
            'stats' => [
                'projects' => $this->projects->countPublishedFor($studio) ?: null,
                'clients' => $clients->count() ?: null,
                'yearsInBusiness' => $this->studios->yearsInBusiness($studio),
            ],
        ]);
    }
}
