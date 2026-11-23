<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Founder\UpdateFounderRequest;
use App\Http\Resources\Founder\FounderFormResource;
use App\Services\Founder\FounderService;
use App\Services\SocialLink\SocialLinkService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the founder profile behind the /founder portfolio.
 */
class FounderController extends Controller
{
    public function __construct(
        private readonly FounderService $founderService,
        private readonly SocialLinkService $socialLinkService,
    ) {}

    /**
     * Show the founder profile form, with the page copy merged over its defaults.
     */
    public function edit(): Response
    {
        $founder = $this->founderService->current();

        return Inertia::render('admin/founder/Edit', [
            'founder' => [
                ...FounderFormResource::make($founder)->resolve(),
                'content' => $this->founderService->content(),
            ],
            'socials' => $this->socialLinkService->asMap($founder),
        ]);
    }

    /**
     * Validate and save the founder profile. The service does the work.
     */
    public function update(UpdateFounderRequest $request): RedirectResponse
    {
        $this->founderService->update($request->validated());
        $this->toast(__('Founder profile updated.'));

        return back();
    }
}
