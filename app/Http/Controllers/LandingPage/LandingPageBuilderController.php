<?php

namespace App\Http\Controllers\LandingPage;

use App\Enums\LandingSectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPage\UpdateLandingPageRequest;
use App\Http\Resources\LandingPage\LandingPageResource;
use App\Models\Project;
use App\Services\LandingPageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the landing page builder of one project.
 */
class LandingPageBuilderController extends Controller
{
    public function __construct(private LandingPageService $landingPages) {}

    /**
     * Show the builder with every block. The page is created on its first
     * save, so the builder can open empty.
     */
    public function edit(Project $project): Response
    {
        $landingPage = $project->landingPage()->with('heroMedia')->first();

        return Inertia::render('landingpage/EditLandingPage', [
            'project' => $project->only(['id', 'title', 'slug']),
            'landingPage' => $landingPage ? LandingPageResource::make($landingPage)->resolve() : null,
            'sections' => $landingPage ? $landingPage->sections()->ordered()->get() : [],
            'sectionTypes' => LandingSectionType::options(),
            'previewUrl' => route('landingPageShow', $project->slug, absolute: false),
        ]);
    }

    /**
     * Save the landing page. It is created on its first save.
     */
    public function update(UpdateLandingPageRequest $request, Project $project): RedirectResponse
    {
        $this->landingPages->save($project, $request->validated());

        $this->toast(__('Landing page saved.'));

        return to_route('landingPageEdit', $project);
    }

    /**
     * Delete the landing page with its blocks and files.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $landingPage = $project->landingPage()->with(['heroMedia', 'sections'])->first();

        if ($landingPage !== null) {
            $this->landingPages->delete($landingPage);
        }

        $this->toast(__('Landing page deleted.'), 'error');

        return to_route('projectIndex');
    }
}
