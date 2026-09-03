<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesMediaUploads;
use App\Enums\LandingSectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LandingPageRequest;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LandingPageController extends Controller
{
    use HandlesMediaUploads;

    /**
     * The landing page builder for a project. The page record is created lazily
     * on first save, so this renders an empty shell until then.
     */
    public function edit(Project $project): Response
    {
        $landingPage = $project->landingPage()
            ->with(['sections' => fn ($query) => $query->ordered()])
            ->first();

        return inertia('admin/projects/landing/Edit', [
            'project' => $project->only(['id', 'title', 'slug']),
            'landingPage' => $landingPage,
            'sections' => $landingPage instanceof ProjectLandingPage
                ? $landingPage->sections
                : [],
            'sectionTypes' => LandingSectionType::options(),
            'previewUrl' => route('landing.show', $project->slug, absolute: false),
        ]);
    }

    public function update(LandingPageRequest $request, Project $project): RedirectResponse
    {
        $landingPage = $project->landingPage()->firstOrNew();

        $landingPage->fill($request->landingAttributes());

        if ($request->boolean('remove_hero_media')) {
            $this->deleteMedia($landingPage->hero_media_path);
            $landingPage->hero_media_path = null;
        }

        if ($request->boolean('remove_og_image')) {
            $this->deleteMedia($landingPage->og_image_path);
            $landingPage->og_image_path = null;
        }

        $landingPage->hero_media_path = $this->storeMedia(
            $request->file('hero_media'), 'landing/hero', $landingPage->hero_media_path,
        );

        $landingPage->og_image_path = $this->storeMedia(
            $request->file('og_image'), 'landing/og', $landingPage->og_image_path,
        );

        // The model's saved/deleted hooks keep projects.has_landing_page in step.
        $project->landingPage()->save($landingPage);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Landing page saved.')]);

        return to_route('admin.projects.landing.edit', $project);
    }

    /**
     * Remove the landing page and everything it owns. Sections cascade at the
     * database level, but their uploaded media has to go explicitly.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $landingPage = $project->landingPage()->with('sections')->first();

        if ($landingPage instanceof ProjectLandingPage) {
            foreach ($landingPage->sections as $section) {
                $this->deleteMediaMany($section->mediaPaths());
            }

            $this->deleteMedia($landingPage->hero_media_path);
            $this->deleteMedia($landingPage->og_image_path);

            $landingPage->delete();
        }

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Landing page deleted.')]);

        return to_route('admin.projects.index');
    }
}
