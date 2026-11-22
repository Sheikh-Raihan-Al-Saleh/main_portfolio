<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FounderRequest;
use App\Services\FounderService;
use App\Services\SocialLinkService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The founder record behind the /founder portfolio.
 */
class FounderController extends Controller
{
    public function __construct(
        private readonly FounderService $founders,
        private readonly SocialLinkService $socialLinks,
    ) {}

    public function edit(): Response
    {
        $founder = $this->founders->current();

        return Inertia::render('admin/founder/Edit', [
            'founder' => [...$founder->toArray(), 'content' => $founder->founder_content],
            'socials' => $this->socialLinks->asMap($founder),
        ]);
    }

    public function update(FounderRequest $request): RedirectResponse
    {
        $this->founders->update($request->validated());

        $this->toast(__('Founder profile updated.'));

        return back();
    }
}
