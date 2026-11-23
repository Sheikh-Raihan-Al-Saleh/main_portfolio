<?php

namespace App\Http\Controllers\Studio;

use App\Enums\LandingSectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Studio\UpdateStudioContentRequest;
use App\Http\Requests\Studio\UpdateStudioRequest;
use App\Http\Resources\Studio\StudioFormResource;
use App\Services\LandingPage\LandingSectionService;
use App\Services\SocialLink\SocialLinkService;
use App\Services\Studio\StudioService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the studio (company) - brand, details, home page blocks and copy.
 */
class StudioController extends Controller
{
    public function __construct(
        private readonly StudioService $studioService,
        private readonly SocialLinkService $socialLinkService,
        private readonly LandingSectionService $sectionService,
    ) {}

    /**
     * Show the "Studio & brand" form with the home page content blocks.
     */
    public function edit(): Response
    {
        $studio = $this->studioService->current();

        return Inertia::render('admin/studio/Edit', [
            'studio' => StudioFormResource::make($studio)->resolve(),
            'socials' => $this->socialLinkService->asMap($studio),
            'sections' => $this->sectionService->allFor($studio),
            'sectionTypes' => LandingSectionType::options(),
        ]);
    }

    /**
     * Validate and save the studio details. The service does the work.
     */
    public function update(UpdateStudioRequest $request): RedirectResponse
    {
        $this->studioService->update($request->validated());
        $this->toast(__('Studio updated.'));

        return back();
    }

    /**
     * Show the editor of the home page copy, merged over its defaults.
     */
    public function editContent(): Response
    {
        $studio = $this->studioService->current();

        return Inertia::render('admin/studio/Content', [
            'studio' => $studio->only(['id', 'name']),
            'content' => $this->studioService->content(),
        ]);
    }

    /**
     * Validate and save the home page copy.
     */
    public function updateContent(UpdateStudioContentRequest $request): RedirectResponse
    {
        $this->studioService->updateContent($request->validated('content'));
        $this->toast(__('Home page copy updated.'));

        return back();
    }
}
