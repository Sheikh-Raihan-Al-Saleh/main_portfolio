<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->string('filter')->trim()->value();

        return inertia('admin/messages/Index', [
            'messages' => ContactMessage::query()
                ->when($filter === 'unread', fn ($query) => $query->unread())
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'filters' => ['filter' => $filter ?: null],
        ]);
    }

    public function show(ContactMessage $message): Response
    {
        // Opening a message is what marks it read.
        $message->markAsRead();

        return inertia('admin/messages/Show', ['message' => $message]);
    }

    public function markUnread(ContactMessage $message): RedirectResponse
    {
        $message->forceFill(['read_at' => null])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Marked as unread.')]);

        return to_route('admin.messages.index');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Message deleted.')]);

        return to_route('admin.messages.index');
    }
}
