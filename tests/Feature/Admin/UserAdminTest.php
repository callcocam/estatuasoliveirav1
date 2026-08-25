<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('lists users with search', function () {
    User::factory()->create(['name' => 'Maria Silva']);

    $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['search' => 'maria']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/Index')
            ->loadDeferredProps(fn ($page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Maria Silva')));
});

test('creates a user with a role', function () {
    $this->actingAs($this->admin)->post(route('admin.users.store'), [
        'name' => 'Novo Gerente',
        'email' => 'gerente@example.com',
        'phone' => null,
        'role' => 'manager',
        'password' => 'senha-muito-segura',
    ]);

    $user = User::query()->where('email', 'gerente@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Manager)
        ->and($user->email_verified_at)->not->toBeNull();
});

test('validates unique email and role', function () {
    $existing = User::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Alguém',
            'email' => $existing->email,
            'role' => 'invalid',
            'password' => 'senha-muito-segura',
        ])
        ->assertSessionHasErrors(['email', 'role']);
});

test('updates a user keeping the password when blank', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    $this->actingAs($this->admin)->put(route('admin.users.update', $user), [
        'name' => 'Nome Atualizado',
        'email' => $user->email,
        'phone' => null,
        'role' => 'customer',
        'password' => '',
    ]);

    expect($user->refresh())
        ->name->toBe('Nome Atualizado')
        ->password->toBe($originalPassword);
});

test('cannot delete their own account', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin))
        ->assertStatus(422);
});

test('deletes another user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

    expect($user->refresh()->trashed())->toBeTrue();
});

test('permanently deletes a trashed user', function () {
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('sends a password reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.users.reset-link', $user));

    Notification::assertSentTo($user, ResetPassword::class);
});

test('lists only trashed users with the trashed filter', function () {
    $trashed = User::factory()->create(['name' => 'Usuário Excluído']);
    $trashed->delete();
    User::factory()->create(['name' => 'Usuário Ativo']);

    $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['trashed' => 'only']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/Index')
            ->loadDeferredProps(fn ($page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Usuário Excluído')
                ->where('users.data.0.deleted', true)));
});

test('hides trashed users from the default listing', function () {
    $trashed = User::factory()->create();
    $trashed->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/Index')
            ->loadDeferredProps(fn ($page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.id', $this->admin->id)));
});

test('restores a trashed user', function () {
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($this->admin)->post(route('admin.users.restore', $user));

    expect($user->refresh()->trashed())->toBeFalse();
});

test('filters users by role', function () {
    User::factory()->create(['role' => 'customer', 'name' => 'Cliente Um']);
    User::factory()->manager()->create(['name' => 'Gerente Um']);

    $this->actingAs($this->admin)
        ->get(route('admin.users.index', ['role' => 'customer']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/Index')
            ->loadDeferredProps(fn ($page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', 'Cliente Um')));
});
