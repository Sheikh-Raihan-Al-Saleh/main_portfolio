<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LandingSectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingPageRequest;
use App\Http\Resources\LandingPageResource;
use App\Models\Project;
use App\Services\LandingPageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The landing page builder for one project.
 */
class LandingPageController extends Controller
{
    public function __construct(
        private readonly LandingPageService $landingPages,
    ) {}

    /**
     * The page record is created on first save, so this can render an empty
     * builder.
     */
    public function edit(Project $project): Response
    {
        $landingPage = $this->landingPages->findForEdit($project);

        return Inertia::render('admin/projects/landing/Edit', [
            'project' => $project->only(['id', 'title', 'slug']),
            'landingPage' => $landingPage ? LandingPageResource::make($landingPage)->resolve() : null,
            'sections' => $landingPage !== null ? $landingPage->sections : [],
            'sectionTypes' => LandingSectionType::options(),
            'previewUrl' => route('landing.show', $project->slug, absolute: false),
        ]);
    }

    public function update(LandingPageRequest $request, Project $project): RedirectResponse
    {
        $this->landingPages->save($project, $request->validated());

        $this->toast(__('Landing page saved.'));

        return to_route('admin.projects.landing.edit', $project);
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->landingPages->delete($project);

        $this->toast(__('Landing page deleted.'), 'error');

        return to_route('admin.projects.index');
    }
}
