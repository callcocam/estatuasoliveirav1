<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuoteController extends Controller
{
    public function index(Request $request): Response
    {
        $quotes = Quote::query()
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->through(fn (Quote $quote): array => [
                'id' => $quote->id,
                'status' => $quote->status->value,
                'statusLabel' => $quote->status->label(),
                'total' => $quote->total,
                'itemsCount' => $quote->items_count,
                'createdAt' => $quote->created_at?->toIso8601String(),
            ]);

        return Inertia::render('quotes/Index', [
            'quotes' => $quotes,
        ]);
    }

    public function show(Request $request, Quote $quote): Response
    {
        abort_unless($quote->user_id === $request->user()->id, 404);

        $quote->load('items.product');

        return Inertia::render('quotes/Show', [
            'quote' => [
                'id' => $quote->id,
                'status' => $quote->status->value,
                'statusLabel' => $quote->status->label(),
                'total' => $quote->total,
                'notes' => $quote->notes,
                'createdAt' => $quote->created_at?->toIso8601String(),
                'items' => $quote->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unitPrice' => $item->unit_price,
                    'total' => $item->total,
                    'productSlug' => $item->product?->slug,
                ])->values(),
            ],
        ]);
    }
}
