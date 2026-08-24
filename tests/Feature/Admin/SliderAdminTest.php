<?php

use App\Models\Slider;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('lists sliders', function () {
    Slider::factory()->count(2)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.sliders.index'))
        ->assertInertia(fn ($page) => $page
            ->component('admin/sliders/Index')
            ->has('sliders', 2));
});

test('creates a slider and redirects to the edit page', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.sliders.store'), [
        'title' => 'Promoção de fontes',
        'status' => 'draft',
    ]);

    $slider = Slider::query()->where('title', 'Promoção de fontes')->firstOrFail();

    $response->assertRedirect(route('admin.sliders.edit', $slider));
});

test('validates the slider payload', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.sliders.store'), ['title' => '', 'status' => 'invalid'])
        ->assertSessionHasErrors(['title', 'status']);
});

test('updates and deletes a slider', function () {
    $slider = Slider::factory()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.sliders.update', $slider), [
            'title' => 'Novo título',
            'status' => 'published',
        ])
        ->assertRedirect();

    expect($slider->refresh()->title)->toBe('Novo título');

    $this->actingAs($this->admin)
        ->delete(route('admin.sliders.destroy', $slider))
        ->assertRedirect(route('admin.sliders.index'));

    expect($slider->refresh()->trashed())->toBeTrue();
});

test('lists only trashed sliders with the trashed filter', function () {
    $trashed = Slider::factory()->create();
    $trashed->delete();
    Slider::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.sliders.index', ['filter' => 'trashed']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/sliders/Index')
            ->has('sliders', 1)
            ->where('sliders.0.id', $trashed->id)
            ->where('sliders.0.deleted', true));
});

test('restores a trashed slider', function () {
    $slider = Slider::factory()->create();
    $slider->delete();

    $this->actingAs($this->admin)->post(route('admin.sliders.restore', $slider));

    expect($slider->refresh()->trashed())->toBeFalse();
});

test('reorders sliders', function () {
    $first = Slider::factory()->create(['sort_order' => 0]);
    $second = Slider::factory()->create(['sort_order' => 1]);

    $this->actingAs($this->admin)
        ->post(route('admin.sliders.reorder'), ['ids' => [$second->id, $first->id]]);

    expect($second->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1);
});

test('filters sliders by status and search', function () {
    Slider::factory()->create(['title' => 'Promo Verao', 'status' => 'published']);
    Slider::factory()->create(['title' => 'Rascunho Inverno', 'status' => 'draft']);

    $this->actingAs($this->admin)
        ->get(route('admin.sliders.index', ['filter' => 'draft']))
        ->assertInertia(fn ($page) => $page->has('sliders', 1));

    $this->actingAs($this->admin)
        ->get(route('admin.sliders.index', ['search' => 'promo']))
        ->assertInertia(fn ($page) => $page->has('sliders', 1));
});
