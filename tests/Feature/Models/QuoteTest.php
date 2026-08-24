<?php

use App\Enums\QuoteStatus;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Support\Str;

test('quotes use ulid primary keys and default to pending', function () {
    $quote = Quote::factory()->create();

    expect(Str::isUlid($quote->id))->toBeTrue()
        ->and($quote->status)->toBe(QuoteStatus::Pending)
        ->and($quote->user)->not->toBeNull();
});

test('a quote recalculates its total from the items', function () {
    $quote = Quote::factory()->create();
    QuoteItem::factory()->for($quote)->create(['quantity' => 2, 'unit_price' => 100, 'total' => 200]);
    QuoteItem::factory()->for($quote)->create(['quantity' => 1, 'unit_price' => 50.50, 'total' => 50.50]);

    $quote->recalculateTotal();

    expect($quote->fresh()->total)->toBe('250.50');
});

test('deleting a product keeps the quote item snapshot', function () {
    $product = Product::factory()->create(['name' => 'Buda Ref: 016']);
    $item = QuoteItem::factory()->for($product)->create(['name' => $product->name]);

    $product->forceDelete();

    $item = $item->fresh();

    expect($item->product_id)->toBeNull()
        ->and($item->name)->toBe('Buda Ref: 016');
});

test('deleting a quote removes its items', function () {
    $item = QuoteItem::factory()->create();

    $item->quote->forceDelete();

    expect(QuoteItem::query()->find($item->id))->toBeNull();
});
