<?php

namespace App\Services\Contact;

use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use App\Services\Founder\FounderService;
use App\Services\Studio\StudioService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

use function Illuminate\Support\defer;

/**
 * Contact module: public enquiries, the owner notification email and the
 * admin inbox.
 */
class ContactMessageService
{
    public function __construct(
        private readonly StudioService $studioService,
        private readonly FounderService $founderService,
    ) {}

    /**
     * Save an enquiry and email the owner after the response is sent.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function receive(array $data, ?string $ip, ?string $userAgent): ContactMessage
    {
        $recipient = ($data['site'] ?? 'studio') === 'founder'
            ? $this->founderService->current()
            : $this->studioService->current();

        $message = new ContactMessage([
            ...collect($data)->only(['name', 'email', 'subject', 'message'])->all(),
            'ip_address' => $ip,
            'user_agent' => str($userAgent)->limit(250)->value(),
        ]);
        $message->recipient()->associate($recipient);
        $message->save();

        $notifyAddress = $recipient->public_email ?? config('mail.from.address');

        // Sent after the response is flushed: a slow mail server never holds
        // up the visitor, and no queue worker has to run.
        defer(fn () => $this->notify($message, $notifyAddress));

        return $message;
    }

    /**
     * The inbox, newest first, 15 per page. Filter "unread" hides read messages.
     *
     * @return LengthAwarePaginator<int, ContactMessage>
     */
    public function list(?string $filter = null): LengthAwarePaginator
    {
        return ContactMessage::query()
            ->when($filter === 'unread', fn ($query) => $query->unread())
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function unreadCount(): int
    {
        return ContactMessage::query()->unread()->count();
    }

    public function markRead(ContactMessage $message): void
    {
        if ($message->read_at === null) {
            $message->forceFill(['read_at' => now()])->save();
        }
    }

    public function markUnread(ContactMessage $message): void
    {
        $message->forceFill(['read_at' => null])->save();
    }

    public function delete(ContactMessage $message): void
    {
        $message->delete();
    }

    /**
     * The message is already saved, so a mail failure must not surface to the
     * visitor as a failed submission: log it and move on.
     */
    private function notify(ContactMessage $message, ?string $address): void
    {
        if (blank($address)) {
            return;
        }

        try {
            Mail::to($address)->send(new NewContactMessage($message, $this->studioService->brand()));
        } catch (Throwable $e) {
            Log::error('Failed to send contact notification.', [
                'contact_message_id' => $message->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
