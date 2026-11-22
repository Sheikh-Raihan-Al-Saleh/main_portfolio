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

        // Handle single hero media (backward compatibility)
        $landingPage->hero_media_path = $this->storeMedia(
            $request->file('hero_media'), 'landing/hero', $landingPage->hero_media_path,
        );

        // The three device slots (desktop, tablet, phone) are positional: the
        // admin preview and the hero frames read them by index, so an empty
        // slot stays as null instead of letting later slots shift down.
        $removeSlots = (array) $request->input('remove_hero_media_slots', []);
        $slotsTouched = $request->hasAny(['hero_media_paths.0', 'hero_media_paths.1', 'hero_media_paths.2'])
            || in_array(true, array_map(boolval(...), $removeSlots), true);

        if ($slotsTouched) {
            $existingPaths = $landingPage->hero_media_paths ?? [];
            $paths = [];

            foreach ([0, 1, 2] as $index) {
                $existing = $existingPaths[$index] ?? null;
                $file = $request->file("hero_media_paths.{$index}");

                if ($file !== null) {
                    $paths[$index] = $this->storeMedia($file, 'landing/hero', $existing);
                } elseif (filter_var($removeSlots[$index] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    $this->deleteMedia($existing);
                    $paths[$index] = null;
                } else {
                    $paths[$index] = $existing;
                }
            }

            $landingPage->hero_media_paths = array_filter($paths) === [] ? null : $paths;

            // The slots supersede the legacy single hero, whose path was copied
            // into slot 0 by migration. Replacing that slot deletes the shared
            // file, so drop the legacy pointer rather than leave it dangling.
            if ($landingPage->hero_media_path !== null && ! in_array($landingPage->hero_media_path, $paths, true)) {
                $this->deleteMedia($landingPage->hero_media_path);
                $landingPage->hero_media_path = null;
            }
        }

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
            $this->deleteMediaMany($landingPage->mediaPaths());

            $landingPage->delete();
        }

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Landing page deleted.')]);

        return to_route('admin.projects.index');
    }
}
