<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Services\Contact\ContactMessageService;
use Illuminate\Http\RedirectResponse;

/**
 * Public: the contact form on the studio and founder pages.
 */
class ContactController extends Controller
{
    public function __construct(
        private readonly ContactMessageService $messageService,
    ) {}

    /**
     * Save a public enquiry and email the site owner. The service does the work.
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        $this->messageService->receive($request->validated(), $request->ip(), $request->userAgent());
        $this->toast(__('Thanks for reaching out. You will hear back soon.'));

        return back();
    }
}
