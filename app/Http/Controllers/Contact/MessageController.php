<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\Contact\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the contact-form inbox.
 */
class MessageController extends Controller
{
    public function __construct(
        private readonly ContactMessageService $messageService,
    ) {}

    /**
     * Show the list of messages.
     */
    public function index(Request $request): Response
    {
        $filter = $request->string('filter')->trim()->value();

        return Inertia::render('admin/messages/Index', [
            'messages' => $this->messageService->list($filter),
            'filters' => ['filter' => $filter ?: null],
        ]);
    }

    /**
     * Opening a message marks it read.
     */
    public function show(ContactMessage $message): Response
    {
        $this->messageService->markRead($message);

        return Inertia::render('admin/messages/Show', ['message' => $message]);
    }

    /**
     * Mark a message as unread again and go back to the inbox.
     */
    public function markUnread(ContactMessage $message): RedirectResponse
    {
        $this->messageService->markUnread($message);
        $this->toast(__('Marked as unread.'));

        return to_route('admin.messages.index');
    }

    /**
     * Delete a message.
     */
    public function destroy(ContactMessage $message): RedirectResponse
    {
        $this->messageService->delete($message);
        $this->toast(__('Message deleted.'), 'error');

        return to_route('admin.messages.index');
    }
}
