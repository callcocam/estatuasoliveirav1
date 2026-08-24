<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QuoteStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuoteStoreRequest;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuoteController extends Controller
{
    public function index(Request $request): Response
    {
        $status = (string) $request->string('status');

        $quotes = Quote::query()
            ->withTrashed()
            ->with('user')
            ->withCount('items')
            ->when($status === 'trashed', fn ($query) => $query->whereNotNull('deleted_at'))
            ->when($status !== '' && $status !== 'trashed', fn ($query) => $query
                ->whereNull('deleted_at')
                ->where('status', $status))
            ->when($status === '', fn ($query) => $query->whereNull('deleted_at'))
            ->latest()
            ->paginate(15)
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

        return Inertia::render('admin/quotes/Index', [
            'quotes' => $quotes,
            'statuses' => $this->statusOptions(),
            'filters' => ['status' => $status !== '' ? $status : null],
        ]);
    }

    public function show(Quote $quote): Response
    {
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
            'productResults' => Inertia::optional(fn () => $search === '' ? [] : Product::query()
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
        $validated = $request->validate([
            'status' => ['required', Rule::enum(QuoteStatus::class)],
        ]);

        $quote->update(['status' => $validated['status']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.quotes.status_updated')]);

        return back();
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.quotes.deleted')]);

        return to_route('admin.quotes.index');
    }

    public function restore(Quote $quote): RedirectResponse
    {
        $quote->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.quotes.restored')]);

        return back();
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
