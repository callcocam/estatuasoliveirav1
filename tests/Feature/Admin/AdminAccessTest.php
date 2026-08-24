<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('customers cannot access the admin panel', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('managers can access content modules', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($manager)->get(route('admin.products.index'))->assertOk();
    $this->actingAs($manager)->get(route('admin.categories.index'))->assertOk();
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
