<?php

use App\Enums\QuoteStatus;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('lists quotes filtered by status', function () {
    Quote::factory()->create(['status' => QuoteStatus::Pending]);
    Quote::factory()->create(['status' => QuoteStatus::Answered]);

    $this->actingAs($this->admin)
        ->get(route('admin.quotes.index', ['status' => 'pending']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/quotes/Index')
            ->has('quotes.data', 1)
            ->where('quotes.data.0.status', 'pending'));
});

test('shows a quote with its items', function () {
    $quote = Quote::factory()->create();
    $quote->items()->create([
        'product_id' => null,
        'name' => 'Estátua sob medida',
        'quantity' => 2,
        'unit_price' => 150,
        'total' => 300,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.quotes.show', $quote))
        ->assertInertia(fn ($page) => $page
            ->component('admin/quotes/Show')
            ->has('quote.items', 1)
            ->where('quote.items.0.name', 'Estátua sob medida'));
});

test('creates a manual quote and recalculates the total', function () {
    $product = Product::factory()->create();
    $customer = User::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.quotes.store'), [
        'user_id' => $customer->id,
        'notes' => 'Entrega combinada',
        'items' => [
            ['product_id' => $product->id, 'name' => $product->name, 'quantity' => 2, 'unit_price' => '100.00'],
            ['product_id' => null, 'name' => 'Frete', 'quantity' => 1, 'unit_price' => '50.00'],
        ],
    ]);

    $quote = Quote::query()->latest()->firstOrFail();

    expect($quote->items()->count())->toBe(2)
        ->and((float) $quote->total)->toBe(250.0)
        ->and($quote->status)->toBe(QuoteStatus::Pending);
});

test('validates manual quote items', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.quotes.store'), ['items' => []])
        ->assertSessionHasErrors(['items']);
});

test('changes the quote status', function () {
    $quote = Quote::factory()->create(['status' => QuoteStatus::Pending]);

    $this->actingAs($this->admin)
        ->patch(route('admin.quotes.status', $quote), ['status' => 'answered'])
        ->assertRedirect();

    expect($quote->refresh()->status)->toBe(QuoteStatus::Answered);
});

test('lists only trashed quotes with the trashed filter', function () {
    $trashed = Quote::factory()->create();
    $trashed->delete();
    Quote::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.quotes.index', ['status' => 'trashed']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/quotes/Index')
            ->has('quotes.data', 1)
            ->where('quotes.data.0.id', $trashed->id)
            ->where('quotes.data.0.deleted', true));
});

test('hides trashed quotes from the default listing', function () {
    $trashed = Quote::factory()->create();
    $trashed->delete();
    $active = Quote::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.quotes.index'))
        ->assertInertia(fn ($page) => $page
            ->component('admin/quotes/Index')
            ->has('quotes.data', 1)
            ->where('quotes.data.0.id', $active->id));
});

test('restores a trashed quote', function () {
    $quote = Quote::factory()->create();
    $quote->delete();

    $this->actingAs($this->admin)->post(route('admin.quotes.restore', $quote));

    expect($quote->refresh()->trashed())->toBeFalse();
});

test('filters quotes by customer search', function () {
    Quote::factory()->for(User::factory()->create(['name' => 'Maria Silva']))->create();
    Quote::factory()->for(User::factory()->create(['name' => 'Joana Prado']))->create();

    $this->actingAs($this->admin)
        ->get(route('admin.quotes.index', ['search' => 'maria']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/quotes/Index')
            ->has('quotes.data', 1)
            ->where('quotes.data.0.userName', 'Maria Silva'));
});
