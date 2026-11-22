<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\ContactRequest;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

/**
 * Receives the public contact forms.
 */
class ContactController extends Controller
{
    public function __construct(
        private readonly ContactMessageService $messages,
    ) {}

    public function store(ContactRequest $request): RedirectResponse
    {
        $this->messages->receive($request->validated(), $request->ip(), $request->userAgent());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Thanks for reaching out. You will hear back soon.'),
        ]);

        return back();
    }
}
