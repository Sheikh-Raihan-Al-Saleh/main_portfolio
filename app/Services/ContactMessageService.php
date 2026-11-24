<?php

namespace App\Services;

use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use App\Models\Founder;
use App\Models\Studio;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Saves public contact-form enquiries and emails the site owner about them.
 */
class ContactMessageService
{
    /**
     * Save an enquiry, addressed to the studio or the founder site.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function store(array $data, ?string $ipAddress, ?string $userAgent): ContactMessage
    {
        $recipient = ($data['site'] ?? 'studio') === 'founder' ? Founder::current() : Studio::current();

        $message = new ContactMessage([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'ip_address' => $ipAddress,
            'user_agent' => Str::limit((string) $userAgent, 250),
        ]);
        $message->recipient()->associate($recipient);
        $message->save();

        return $message;
    }

    /**
     * Email the site owner about an enquiry: to the recipient's public email,
     * or the app's "from" address when it has none.
     *
     * The message is already saved, so a mail failure must not surface to the
     * visitor as a failed submission: it is logged instead.
     */
    public function notifyOwner(ContactMessage $message): void
    {
        $recipient = $message->loadMissing('recipient')->recipient;
        $address = $recipient?->getAttribute('public_email') ?? config('mail.from.address');

        if (blank($address)) {
            return;
        }

        try {
            Mail::to($address)->send(new NewContactMessage($message, Studio::brand()));
        } catch (Throwable $exception) {
            Log::error('Failed to send contact notification.', [
                'contact_message_id' => $message->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
