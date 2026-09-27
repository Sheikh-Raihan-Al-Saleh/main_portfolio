<?php

namespace App\Http\Controllers;

use App\Actions\PresentsLandingSection;
use App\Models\LandingSection;
use App\Models\Project;
use Inertia\Response;

class LandingPageController extends Controller
{
    /**
     * A project's marketing landing page.
     *
     * Both the project and its landing page must be published: an unpublished
     * case study is a draft, and drafts 404 rather than redirecting, so their
     * existence is not disclosed.
     */
    public function show(Project $project): Response
    {
        abort_unless($project->is_published, 404);

        $landingPage = $project->landingPage()->published()->first();

        abort_if($landingPage === null, 404);

        $present = app(PresentsLandingSection::class);

        $sections = $landingPage->sections()
            ->visible()
            ->ordered()
            ->get()
            ->map(fn (LandingSection $section): array => $present($section));

        return inertia('public/landing/Show', [
            'project' => $project,
            'landingPage' => $landingPage,
            'sections' => $sections,
        ]);
    }
}
