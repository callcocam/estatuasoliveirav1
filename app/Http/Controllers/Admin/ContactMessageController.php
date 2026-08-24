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
        $filter = (string) $request->string('filter');

        $messages = ContactMessage::query()
            ->when($filter === 'unread', fn ($query) => $query->unread())
            ->when($filter === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ContactMessage $message): array => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'subject' => $message->subject,
                'read' => $message->isRead(),
                'createdAt' => $message->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/messages/Index', [
            'messages' => $messages,
            'filters' => ['filter' => $filter !== '' ? $filter : null],
        ]);
    }

    public function show(ContactMessage $message): Response
    {
        $message->markAsRead();

        return Inertia::render('admin/messages/Show', [
            'message' => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'phone' => $message->phone,
                'subject' => $message->subject,
                'message' => $message->message,
                'read' => $message->isRead(),
                'createdAt' => $message->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function toggleRead(ContactMessage $message): RedirectResponse
    {
        $message->update(['read_at' => $message->isRead() ? null : now()]);

        return back();
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.messages.deleted')]);

        return to_route('admin.messages.index');
    }
}
