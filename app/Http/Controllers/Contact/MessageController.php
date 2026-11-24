<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the contact-form inbox.
 */
class MessageController extends Controller
{
    /**
     * Show the inbox, newest first, 15 per page. Filter "unread" hides read messages.
     */
    public function index(Request $request): Response
    {
        $filter = $request->string('filter')->trim()->value();

        $query = ContactMessage::latest();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $messages = $query->paginate(15)->withQueryString();

        return Inertia::render('contact/MessageIndex', [
            'messages' => $messages,
            'filters' => ['filter' => $filter ?: null],
        ]);
    }

    /**
     * Show a message. Opening it marks it read.
     */
    public function show(ContactMessage $message): Response
    {
        if ($message->read_at === null) {
            $message->forceFill(['read_at' => now()])->save();
        }

        return Inertia::render('contact/ShowMessage', ['message' => $message]);
    }

    /**
     * Mark a message as unread again and go back to the inbox.
     */
    public function markUnread(ContactMessage $message): RedirectResponse
    {
        $message->forceFill(['read_at' => null])->save();
        $this->toast(__('Marked as unread.'));

        return to_route('messageIndex');
    }

    /**
     * Delete a message.
     */
    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();
        $this->toast(__('Message deleted.'), 'error');

        return to_route('messageIndex');
    }
}
