<?php

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('quotes.index'))->assertRedirect(route('login'));
});

test('customers can view their own quotes list', function () {
    $user = User::factory()->create();
    $quote = Quote::factory()->create(['user_id' => $user->id]);
    QuoteItem::factory()->count(2)->create(['quote_id' => $quote->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('quotes.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('quotes/Index')
        ->has('quotes.data', 1)
        ->where('quotes.data.0.id', $quote->id)
        ->where('quotes.data.0.status', $quote->status->value)
        ->where('quotes.data.0.statusLabel', $quote->status->label())
        ->where('quotes.data.0.itemsCount', 2),
    );
});

test('quotes list does not include quotes from other users', function () {
    $user = User::factory()->create();
    Quote::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('quotes.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('quotes/Index')
        ->has('quotes.data', 0),
    );
});

test('quotes list does not include soft-deleted quotes', function () {
    $user = User::factory()->create();
    $quote = Quote::factory()->create(['user_id' => $user->id]);
    $quote->delete();

    $response = $this
        ->actingAs($user)
        ->get(route('quotes.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('quotes/Index')
        ->has('quotes.data', 0),
    );
});

test('customers can view the details of their own quote', function () {
    $user = User::factory()->create();
    $quote = Quote::factory()->create(['user_id' => $user->id]);
    $item = QuoteItem::factory()->create(['quote_id' => $quote->id]);

    $response = $this
        ->actingAs($user)
        ->get(route('quotes.show', $quote));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('quotes/Show')
        ->where('quote.id', $quote->id)
        ->where('quote.statusLabel', $quote->status->label())
        ->has('quote.items', 1)
        ->where('quote.items.0.id', $item->id)
        ->where('quote.items.0.name', $item->name),
    );
});

test('customers cannot view quotes that belong to other users', function () {
    $user = User::factory()->create();
    $quote = Quote::factory()->create();

    $this
        ->actingAs($user)
        ->get(route('quotes.show', $quote))
        ->assertNotFound();
});

test('unverified users are redirected to the verification notice', function () {
    $user = User::factory()->unverified()->create();

    $this
        ->actingAs($user)
        ->get(route('quotes.index'))
        ->assertRedirect(route('verification.notice'));
});
