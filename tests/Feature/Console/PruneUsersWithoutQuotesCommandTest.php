<?php

use App\Models\Quote;
use App\Models\User;

test('soft deletes customers without quotes and keeps the rest', function () {
    $withoutQuote = User::factory()->create();
    $withQuote = User::factory()->create();
    Quote::factory()->for($withQuote)->create();
    $admin = User::factory()->admin()->create();

    $this->artisan('users:prune-without-quotes')->assertSuccessful();

    expect($withoutQuote->refresh()->trashed())->toBeTrue()
        ->and($withQuote->refresh()->trashed())->toBeFalse()
        ->and($admin->refresh()->trashed())->toBeFalse();
});

test('keeps customers whose only quote is soft deleted', function () {
    $user = User::factory()->create();
    Quote::factory()->for($user)->create()->delete();

    $this->artisan('users:prune-without-quotes')->assertSuccessful();

    expect($user->refresh()->trashed())->toBeFalse();
});

test('dry run does not delete anyone', function () {
    $user = User::factory()->create();

    $this->artisan('users:prune-without-quotes', ['--dry-run' => true])->assertSuccessful();

    expect($user->refresh()->trashed())->toBeFalse();
});
