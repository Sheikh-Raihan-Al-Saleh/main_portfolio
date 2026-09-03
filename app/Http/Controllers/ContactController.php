<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function store(ContactMessageRequest $request): RedirectResponse
    {
        $message = ContactMessage::create([
            ...$request->safe()->only(['name', 'email', 'subject', 'message']),
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent())->limit(250)->value(),
        ]);

        $this->notifyOwner($message);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Thanks for reaching out — I will get back to you soon.'),
        ]);

        return back();
    }

    /**
     * The message is already saved, so a mail failure must not surface to the
     * visitor as a failed submission -- log it and move on.
     */
    private function notifyOwner(ContactMessage $message): void
    {
        $recipient = Profile::current()->public_email ?? config('mail.from.address');

        if (blank($recipient)) {
            return;
        }

        try {
            Mail::to($recipient)->send(new NewContactMessage($message));
        } catch (\Throwable $e) {
            Log::error('Failed to send contact notification.', [
                'contact_message_id' => $message->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
