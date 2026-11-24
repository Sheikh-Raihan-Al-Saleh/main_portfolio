<?php

namespace App\Http\Controllers\Studio;

use App\Enums\LandingSectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Studio\UpdateStudioContentRequest;
use App\Http\Requests\Studio\UpdateStudioRequest;
use App\Http\Resources\Studio\StudioFormResource;
use App\Models\Studio;
use App\Services\StudioService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the studio (company) - brand, details, home page blocks and copy.
 */
class StudioController extends Controller
{
    public function __construct(private StudioService $studios) {}

    /**
     * Show the "Studio & brand" form with the home page content blocks.
     */
    public function edit(): Response
    {
        $studio = Studio::current();

        return Inertia::render('studio/EditStudio', [
            'studio' => StudioFormResource::make($studio)->resolve(),
            'socials' => $studio->socialLinkMap(),
            'sections' => $studio->sections()->ordered()->get(),
            'sectionTypes' => LandingSectionType::options(),
        ]);
    }

    /**
     * Save the studio details, logos and social links.
     */
    public function update(UpdateStudioRequest $request): RedirectResponse
    {
        $this->studios->update(Studio::current(), $request->validated());

        $this->toast(__('Studio updated.'));

        return back();
    }

    /**
     * Show the editor of the home page copy, merged over its defaults.
     */
    public function editContent(): Response
    {
        $studio = Studio::current();

        return Inertia::render('studio/StudioContent', [
            'studio' => $studio->only(['id', 'name']),
            'content' => $studio->pageContent(),
        ]);
    }

    /**
     * Save the home page copy. It is merged over the defaults whenever it is
     * read (see Studio::pageContent()).
     */
    public function updateContent(UpdateStudioContentRequest $request): RedirectResponse
    {
        Studio::current()->update(['content' => $request->validated('content')]);

        $this->toast(__('Home page copy updated.'));

        return back();
    }
}
