<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithDeferredIndex;
use App\Http\Controllers\Concerns\InteractsWithResourceAbilities;
use App\Http\Controllers\Concerns\InteractsWithTrashedFilter;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    use InteractsWithDeferredIndex;
    use InteractsWithResourceAbilities;
    use InteractsWithTrashedFilter;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ContactMessage::class);

        return $this->renderDeferredIndex(
            'admin/messages/Index',
            'messages',
            fn (): LengthAwarePaginator => $this->messagesPaginator($request),
            [
                'filters' => [
                    'search' => (string) $request->string('search'),
                    'read' => (string) $request->string('read'),
                    'trashed' => $this->resolveTrashedFilter($request),
                    'per_page' => (string) $this->resolvePerPage($request),
                ],
                'can' => $this->resolveResourceAbilities(ContactMessage::class),
            ],
        );
    }

    public function show(ContactMessage $message): Response
    {
        $this->authorize('view', $message);

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
        $this->authorize('update', $message);

        $message->update(['read_at' => $message->isRead() ? null : now()]);

        return back();
    }

    /**
     * Soft delete on the first call; permanently delete when the message is
     * already trashed.
     */
    public function destroy(ContactMessage $message): RedirectResponse
    {
        $this->authorize('delete', $message);

        if ($message->trashed()) {
            $message->forceDelete();

            Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.messages.force_deleted')]);

            return to_route('admin.messages.index', ['trashed' => 'only']);
        }

        $message->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.messages.deleted')]);

        return to_route('admin.messages.index');
    }

    public function restore(ContactMessage $message): RedirectResponse
    {
        $this->authorize('delete', $message);

        $message->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.messages.restored')]);

        return back();
    }

    private function messagesPaginator(Request $request): LengthAwarePaginator
    {
        $read = (string) $request->string('read');
        $search = (string) $request->string('search');
        $trashed = $this->resolveTrashedFilter($request);

        return $this->applyTrashedToQuery(ContactMessage::query(), $trashed)
            ->when($read === 'unread', fn ($query) => $query->unread())
            ->when($read === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($search !== '', fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%'.mb_strtolower($search).'%'])))
            ->latest()
            ->orderBy('id', 'desc')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString()
            ->through(fn (ContactMessage $message): array => [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'subject' => $message->subject,
                'read' => $message->isRead(),
                'createdAt' => $message->created_at?->toIso8601String(),
                'deleted' => $message->trashed(),
            ]);
    }
}
