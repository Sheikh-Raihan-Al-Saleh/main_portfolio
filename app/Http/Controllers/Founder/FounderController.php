<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Founder\UpdateFounderRequest;
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

    public function edit(): Response
    {
        $founder = $this->founderService->current();

        return Inertia::render('admin/founder/Edit', [
            'founder' => [...$founder->toArray(), 'content' => $founder->founder_content],
            'socials' => $this->socialLinkService->asMap($founder),
        ]);
    }

    public function update(UpdateFounderRequest $request): RedirectResponse
    {
        $this->founderService->update($request->validated());
        $this->toast(__('Founder profile updated.'));

        return back();
    }
}
