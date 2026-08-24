<?php

use App\Models\ContactMessage;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('lists messages filtered by read state', function () {
    ContactMessage::factory()->create();
    ContactMessage::factory()->read()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.messages.index', ['filter' => 'unread']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/messages/Index')
            ->has('messages.data', 1)
            ->where('messages.data.0.read', false));
});

test('viewing a message marks it as read', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.messages.show', $message))
        ->assertInertia(fn ($page) => $page->component('admin/messages/Show'));

    expect($message->refresh()->isRead())->toBeTrue();
});

test('toggles the read state', function () {
    $message = ContactMessage::factory()->read()->create();

    $this->actingAs($this->admin)->patch(route('admin.messages.read', $message));

    expect($message->refresh()->isRead())->toBeFalse();
});

test('deletes a message', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.messages.destroy', $message))
        ->assertRedirect(route('admin.messages.index'));

    expect(ContactMessage::query()->count())->toBe(0)
        ->and(ContactMessage::withTrashed()->count())->toBe(1);
});

test('lists only trashed messages with the trashed filter', function () {
    $trashed = ContactMessage::factory()->create();
    $trashed->delete();
    ContactMessage::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.messages.index', ['filter' => 'trashed']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/messages/Index')
            ->has('messages.data', 1)
            ->where('messages.data.0.id', $trashed->id)
            ->where('messages.data.0.deleted', true));
});

test('restores a trashed message', function () {
    $message = ContactMessage::factory()->create();
    $message->delete();

    $this->actingAs($this->admin)->post(route('admin.messages.restore', $message));

    expect($message->refresh()->trashed())->toBeFalse();
});

test('filters messages by search', function () {
    ContactMessage::factory()->create(['name' => 'Carlos Souza']);
    ContactMessage::factory()->create(['name' => 'Ana Lima']);

    $this->actingAs($this->admin)
        ->get(route('admin.messages.index', ['search' => 'carlos']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/messages/Index')
            ->has('messages.data', 1)
            ->where('messages.data.0.name', 'Carlos Souza'));
});
