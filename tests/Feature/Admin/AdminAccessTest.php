<?php

use App\Models\User;

dataset('admin get routes', [
    'admin.dashboard',
    'admin.help',
    'admin.categories.index',
    'admin.products.index',
    'admin.sliders.index',
    'admin.quotes.index',
    'admin.messages.index',
    'admin.users.index',
    'admin.settings.edit',
]);

test('guests are redirected to the login page', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('admin get routes');

test('customers cannot access any admin module', function (string $routeName) {
    $this->actingAs(User::factory()->create())
        ->get(route($routeName))
        ->assertForbidden();
})->with('admin get routes');

test('customers cannot upload media through the admin endpoint', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.media.store'))
        ->assertForbidden();
});

test('managers can access content modules', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($manager)->get(route('admin.products.index'))->assertOk();
    $this->actingAs($manager)->get(route('admin.categories.index'))->assertOk();
});

test('managers can read the help page with its guide sections', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get(route('admin.help'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Help')
            ->has('intro')
            ->has('sections', 10));
});

test('managers cannot access users or settings', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($manager)->get(route('admin.settings.edit'))->assertForbidden();
});

test('admins can access every module', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk();
});

test('the admin dashboard shows stats and latest activity', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn ($page) => $page
            ->component('admin/Dashboard')
            ->has('stats')
            ->has('latestMessages')
            ->has('latestQuotes'));
});
