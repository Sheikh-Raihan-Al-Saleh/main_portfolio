<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Tells the site owner about a new contact-form enquiry.
 */
class NewContactMessage extends Mailable
{
    /**
     * @param  array{name: string, logo_url: string|null}  $brand  The studio brand (see Studio::brand()).
     */
    public function __construct(
        public ContactMessage $contactMessage,
        public array $brand,
    ) {}

    /**
     * The subject line and reply-to address.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New enquiry for {$this->brand['name']} from {$this->contactMessage->name}",
            // Replying in a mail client goes straight back to the sender.
            replyTo: [$this->contactMessage->email],
        );
    }

    /**
     * The markdown email body and the brand shown in it.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contact-message',
            with: [
                'brandName' => $this->brand['name'],
                // Email clients need absolute URLs.
                'brandLogo' => $this->brand['logo_url'] ? url($this->brand['logo_url']) : null,
            ],
        );
    }
}
