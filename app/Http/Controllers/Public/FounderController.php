<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectCardResource;
use App\Services\FounderService;
use App\Services\ProjectService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The founder's personal portfolio (/founder).
 */
class FounderController extends Controller
{
    public function __construct(
        private readonly FounderService $founders,
        private readonly ProjectService $projects,
    ) {}

    public function __invoke(): Response
    {
        $founder = $this->founders->loadPortfolio($this->founders->current());
        $projects = $this->projects->publishedFor($founder);

        return Inertia::render('public/Founder', [
            'content' => $founder->founder_content,
            'skillGroups' => $founder->skillCategories,
            'experiences' => $founder->experiences,
            'educations' => $founder->educations,
            'projects' => ProjectCardResource::collection($projects)->resolve(),
            'stats' => $this->founders->stats($founder, $projects->count()),
        ]);
    }
}
