<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\LandingPage\LandingSectionResource;
use App\Http\Resources\Project\ProjectCardResource;
use App\Models\Founder;
use App\Models\Project;
use App\Models\Studio;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public studio pages: the home page (/) and the About page (/about).
 */
class StudioPageController extends Controller
{
    /**
     * Show the studio home page.
     */
    public function home(): Response
    {
        $studio = Studio::current();
        $founder = Founder::current();
        $clients = $studio->clients()->visible()->ordered()->get();
        $totalProjects = $this->countPublished($studio);

        return Inertia::render('studio/HomePage', [
            'content' => $studio->pageContent(),
            'projects' => ProjectCardResource::collection($this->publishedCards($studio, 6))->resolve(),
            'totalProjects' => $totalProjects,
            'founderProjects' => ProjectCardResource::collection($this->publishedCards($founder, 3))->resolve(),
            'totalFounderProjects' => $this->countPublished($founder),
            'clients' => ClientResource::collection($clients)->resolve(),
            'sections' => LandingSectionResource::collection($studio->sections()->visible()->ordered()->get())->resolve(),
            'stats' => $this->stats($studio, $totalProjects, $clients->count()),
        ]);
    }

    /**
     * Show the studio about page.
     */
    public function about(): Response
    {
        $studio = Studio::current();
        $clients = $studio->clients()->visible()->ordered()->get();

        return Inertia::render('studio/AboutPage', [
            'clients' => ClientResource::collection($clients)->resolve(),
            'stats' => $this->stats($studio, $this->countPublished($studio), $clients->count()),
        ]);
    }

    /**
     * The first published project cards of one owner, in display order.
     *
     * @return Collection<int, Project>
     */
    private function publishedCards(Studio|Founder $owner, int $limit): Collection
    {
        return Project::forCards()->published()->ownedBy($owner)->ordered()->limit($limit)->get();
    }

    /**
     * How many published projects one owner (studio or founder) has.
     */
    private function countPublished(Studio|Founder $owner): int
    {
        return Project::published()->ownedBy($owner)->count();
    }

    /**
     * Headline numbers for the public pages. A zero becomes null, so a page
     * never prints "0 clients".
     *
     * @return array{projects: int|null, clients: int|null, yearsInBusiness: int|null}
     */
    private function stats(Studio $studio, int $publishedProjects, int $visibleClients): array
    {
        $founded = $studio->founded_year;

        return [
            'projects' => $publishedProjects ?: null,
            'clients' => $visibleClients ?: null,
            'yearsInBusiness' => $founded && $founded <= now()->year ? now()->year - $founded : null,
        ];
    }
}
