<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LandingSectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudioContentRequest;
use App\Http\Requests\Admin\StudioRequest;
use App\Services\LandingSectionService;
use App\Services\SocialLinkService;
use App\Services\StudioService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The studio (company) record: brand, story, contact, home page blocks and
 * the home page's fixed-section copy.
 */
class StudioController extends Controller
{
    public function __construct(
        private readonly StudioService $studios,
        private readonly SocialLinkService $socialLinks,
        private readonly LandingSectionService $sections,
    ) {}

    public function edit(): Response
    {
        $studio = $this->studios->current();

        return Inertia::render('admin/studio/Edit', [
            'studio' => $studio,
            'socials' => $this->socialLinks->asMap($studio),
            'sections' => $this->sections->allFor($studio),
            'sectionTypes' => LandingSectionType::options(),
        ]);
    }

    public function update(StudioRequest $request): RedirectResponse
    {
        $this->studios->update($request->validated());

        $this->toast(__('Studio updated.'));

        return back();
    }

    public function editContent(): Response
    {
        $studio = $this->studios->current();

        return Inertia::render('admin/studio/Content', [
            'studio' => $studio->only(['id', 'name']),
            'content' => $studio->studio_content,
        ]);
    }

    public function updateContent(StudioContentRequest $request): RedirectResponse
    {
        $this->studios->updateContent($request->validated('content'));

        $this->toast(__('Home page copy updated.'));

        return back();
    }
}
