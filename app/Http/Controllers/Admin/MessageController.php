<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The contact-form inbox.
 */
class MessageController extends Controller
{
    public function __construct(
        private readonly ContactMessageService $messages,
    ) {}

    public function index(Request $request): Response
    {
        $filter = $request->string('filter')->trim()->value();

        return Inertia::render('admin/messages/Index', [
            'messages' => $this->messages->inbox($filter),
            'filters' => ['filter' => $filter ?: null],
        ]);
    }

    /**
     * Opening a message marks it read.
     */
    public function show(ContactMessage $message): Response
    {
        $this->messages->markRead($message);

        return Inertia::render('admin/messages/Show', ['message' => $message]);
    }

    public function markUnread(ContactMessage $message): RedirectResponse
    {
        $this->messages->markUnread($message);

        $this->toast(__('Marked as unread.'));

        return to_route('admin.messages.index');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $this->messages->delete($message);

        $this->toast(__('Message deleted.'), 'error');

        return to_route('admin.messages.index');
    }
}
