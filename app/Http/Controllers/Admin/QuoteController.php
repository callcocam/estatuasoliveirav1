<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Concerns\InteractsWithDeferredIndex;
use App\Http\Controllers\Concerns\InteractsWithResourceAbilities;
use App\Http\Controllers\Concerns\InteractsWithTrashedFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuoteStoreRequest;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuoteController extends Controller
{
    use InteractsWithDeferredIndex;
    use InteractsWithResourceAbilities;
    use InteractsWithTrashedFilter;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Quote::class);

        return $this->renderDeferredIndex(
            'admin/quotes/Index',
            'quotes',
            fn (): LengthAwarePaginator => $this->quotesPaginator($request),
            [
                'statuses' => $this->statusOptions(),
                'filters' => [
                    'search' => (string) $request->string('search'),
                    'status' => (string) $request->string('status'),
                    'trashed' => $this->resolveTrashedFilter($request),
                    'per_page' => (string) $this->resolvePerPage($request),
                ],
                'can' => $this->resolveResourceAbilities(Quote::class),
            ],
        );
    }

    public function show(Quote $quote): Response
    {
        $this->authorize('view', $quote);

        $quote->load(['user', 'items.product']);

        return Inertia::render('admin/quotes/Show', [
            'quote' => [
                'id' => $quote->id,
                'status' => $quote->status->value,
                'statusLabel' => $quote->status->label(),
                'total' => $quote->total,
                'notes' => $quote->notes,
                'createdAt' => $quote->created_at?->toIso8601String(),
                'user' => $quote->user ? [
                    'name' => $quote->user->name,
                    'email' => $quote->user->email,
                    'phone' => $quote->user->phone,
                ] : null,
                'items' => $quote->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unitPrice' => $item->unit_price,
                    'total' => $item->total,
                    'productSlug' => $item->product?->slug,
                ])->values(),
            ],
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Quote::class);

        $search = (string) $request->string('search');

        return Inertia::render('admin/quotes/Create', [
            'users' => User::query()
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]),
            'productResults' => Inertia::optional(fn () => $search === '' ? collect() : Product::query()
                ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                ])),
        ]);
    }

    public function store(QuoteStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $quote = Quote::query()->create([
            'user_id' => $data['user_id'] ?? null,
            'status' => QuoteStatus::Pending,
            'total' => 0,
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $quote->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => round($item['quantity'] * (float) $item['unit_price'], 2),
            ]);
        }

        $quote->recalculateTotal();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.quotes.created')]);

        return to_route('admin.quotes.show', $quote);
    }

    public function updateStatus(Request $request, Quote $quote): RedirectResponse
    {
        $this->authorize('update', $quote);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(QuoteStatus::class)],
        ]);

        $quote->update(['status' => $validated['status']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.quotes.status_updated')]);

        return back();
    }

    /**
     * Soft delete on the first call; permanently delete (items cascade at the
     * database level) when the quote is already trashed.
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $this->authorize('delete', $quote);

        if ($quote->trashed()) {
            $quote->forceDelete();

            Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.quotes.force_deleted')]);

            return to_route('admin.quotes.index', ['trashed' => 'only']);
        }

        $quote->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.quotes.deleted')]);

        return to_route('admin.quotes.index');
    }

    public function restore(Quote $quote): RedirectResponse
    {
        $this->authorize('delete', $quote);

        $quote->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.quotes.restored')]);

        return back();
    }

    private function quotesPaginator(Request $request): LengthAwarePaginator
    {
        $status = (string) $request->string('status');
        $search = (string) $request->string('search');
        $trashed = $this->resolveTrashedFilter($request);

        return $this->applyTrashedToQuery(Quote::query(), $trashed)
            ->with(['user' => fn ($query) => $query->withoutGlobalScope(SoftDeletingScope::class)])
            ->withCount('items')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query
                ->whereHas('user', fn ($userQuery) => $userQuery
                    ->withoutGlobalScope(SoftDeletingScope::class)
                    ->where(fn ($searchQuery) => $searchQuery
                        ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                        ->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($search).'%']))))
            ->latest()
            ->orderBy('id', 'desc')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString()
            ->through(fn (Quote $quote): array => [
                'id' => $quote->id,
                'userName' => $quote->user?->name,
                'status' => $quote->status->value,
                'statusLabel' => $quote->status->label(),
                'total' => $quote->total,
                'itemsCount' => $quote->items_count,
                'createdAt' => $quote->created_at?->toIso8601String(),
                'deleted' => $quote->trashed(),
            ]);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return array_map(fn (QuoteStatus $status): array => [
            'value' => $status->value,
            'label' => $status->label(),
        ], QuoteStatus::cases());
    }
}
