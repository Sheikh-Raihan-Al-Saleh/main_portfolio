<?php

namespace App\Http\Controllers\LandingPage;

use App\Enums\LandingSectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPage\UpdateLandingPageRequest;
use App\Http\Resources\LandingPage\LandingPageResource;
use App\Models\Project;
use App\Services\LandingPage\LandingPageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the landing page builder of one project.
 */
class LandingPageBuilderController extends Controller
{
    public function __construct(
        private readonly LandingPageService $landingPageService,
    ) {}

    /**
     * The page is created on first save, so the builder can open empty.
     */
    public function edit(Project $project): Response
    {
        $landingPage = $this->landingPageService->findForEdit($project);

        return Inertia::render('admin/projects/landing/Edit', [
            'project' => $project->only(['id', 'title', 'slug']),
            'landingPage' => $landingPage ? LandingPageResource::make($landingPage)->resolve() : null,
            'sections' => $landingPage ? $landingPage->sections : [],
            'sectionTypes' => LandingSectionType::options(),
            'previewUrl' => route('landing.show', $project->slug, absolute: false),
        ]);
    }

    /**
     * Validate and save the landing page. It is created on its first save.
     */
    public function update(UpdateLandingPageRequest $request, Project $project): RedirectResponse
    {
        $this->landingPageService->save($project, $request->validated());
        $this->toast(__('Landing page saved.'));

        return to_route('admin.projects.landing.edit', $project);
    }

    /**
     * Delete the landing page with its blocks and files.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->landingPageService->delete($project);
        $this->toast(__('Landing page deleted.'), 'error');

        return to_route('admin.projects.index');
    }
}
