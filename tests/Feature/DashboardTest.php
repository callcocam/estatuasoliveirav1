<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('customers visiting the dashboard are redirected to their quotes', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertRedirect(route('quotes.index', absolute: false));
});

test('admins visiting the dashboard are redirected to the admin panel', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('managers visiting the dashboard are redirected to the admin panel', function () {
    $manager = User::factory()->manager()->create();

    $response = $this
        ->actingAs($manager)
        ->get(route('dashboard'));

    $response->assertRedirect(route('admin.dashboard', absolute: false));
});
