<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;

use function Illuminate\Support\defer;

/**
 * Public: the contact form on the studio and founder pages.
 */
class ContactController extends Controller
{
    public function __construct(private ContactMessageService $messages) {}

    /**
     * Save a public enquiry and email the site owner after the response is sent.
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        $message = $this->messages->store($request->validated(), $request->ip(), $request->userAgent());

        // Sent after the response is flushed: a slow mail server never holds
        // up the visitor, and no queue worker has to run.
        defer(fn () => $this->messages->notifyOwner($message));

        $this->toast(__('Thanks for reaching out. You will hear back soon.'));

        return back();
    }
}
