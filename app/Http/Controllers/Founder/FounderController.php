<?php

namespace App\Http\Controllers\Founder;

use App\Http\Controllers\Controller;
use App\Http\Requests\Founder\UpdateFounderRequest;
use App\Http\Resources\Founder\FounderFormResource;
use App\Models\Founder;
use App\Services\FounderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the founder profile behind the /founder portfolio.
 */
class FounderController extends Controller
{
    public function __construct(private FounderService $founders) {}

    /**
     * Show the founder profile form, with the page copy merged over its defaults.
     */
    public function edit(): Response
    {
        $founder = Founder::current();

        return Inertia::render('founder/EditFounder', [
            'founder' => [
                ...FounderFormResource::make($founder)->resolve(),
                'content' => $founder->pageContent(),
            ],
            'socials' => $founder->socialLinkMap(),
        ]);
    }

    /**
     * Save the founder profile: details, media files and social links.
     */
    public function update(UpdateFounderRequest $request): RedirectResponse
    {
        $this->founders->update(Founder::current(), $request->validated());

        $this->toast(__('Founder profile updated.'));

        return back();
    }
}
