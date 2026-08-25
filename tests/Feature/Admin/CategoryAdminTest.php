<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('lists only active categories by default', function () {
    Category::factory()->create(['name' => 'Fontes']);
    Category::factory()->create(['name' => 'Vasos'])->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.categories.index'))
        ->assertInertia(fn ($page) => $page
            ->component('admin/categories/Index')
            ->has('categories', 1));
});

test('filters categories by trashed, status and search', function () {
    Category::factory()->create(['name' => 'Fontes', 'status' => 'published']);
    Category::factory()->create(['name' => 'Estatuas', 'status' => 'draft']);
    Category::factory()->create(['name' => 'Vasos'])->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.categories.index', ['trashed' => 'only']))
        ->assertInertia(fn ($page) => $page->has('categories', 1));

    $this->actingAs($this->admin)
        ->get(route('admin.categories.index', ['trashed' => 'with']))
        ->assertInertia(fn ($page) => $page->has('categories', 3));

    $this->actingAs($this->admin)
        ->get(route('admin.categories.index', ['status' => 'draft']))
        ->assertInertia(fn ($page) => $page->has('categories', 1));

    $this->actingAs($this->admin)
        ->get(route('admin.categories.index', ['search' => 'font']))
        ->assertInertia(fn ($page) => $page->has('categories', 1));
});

test('creates a category generating a unique slug', function () {
    Category::factory()->create(['slug' => 'fontes']);

    $this->actingAs($this->admin)
        ->post(route('admin.categories.store'), [
            'name' => 'Fontes',
            'slug' => '',
            'description' => null,
            'status' => 'published',
        ])
        ->assertRedirect();

    expect(Category::query()->where('slug', 'fontes-2')->exists())->toBeTrue();
});

test('validates required fields when creating a category', function () {
    $this->actingAs($this->admin)
        ->from(route('admin.categories.index'))
        ->post(route('admin.categories.store'), ['name' => '', 'status' => 'nope'])
        ->assertSessionHasErrors(['name', 'status']);
});

test('updates a category', function () {
    $category = Category::factory()->create(['name' => 'Antigo']);

    $this->actingAs($this->admin)
        ->put(route('admin.categories.update', $category), [
            'name' => 'Novo nome',
            'slug' => $category->slug,
            'description' => 'Descrição',
            'status' => 'draft',
        ])
        ->assertRedirect();

    expect($category->refresh())
        ->name->toBe('Novo nome')
        ->description->toBe('Descrição');
});

test('soft deletes and restores a category', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
    expect($category->refresh()->trashed())->toBeTrue();

    $this->actingAs($this->admin)->post(route('admin.categories.restore', $category->slug));
    expect($category->refresh()->trashed())->toBeFalse();
});

test('permanently deletes a trashed category keeping its products', function () {
    $category = Category::factory()->trashed()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.categories.destroy', $category->slug))
        ->assertRedirect();

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    expect($product->refresh()->category_id)->toBeNull();
});

test('customers cannot manage categories', function () {
    $customer = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($customer)->get(route('admin.categories.index'))->assertForbidden();
    $this->actingAs($customer)->delete(route('admin.categories.destroy', $category))->assertForbidden();
});

test('reorders categories', function () {
    $first = Category::factory()->create(['sort_order' => 0]);
    $second = Category::factory()->create(['sort_order' => 1]);

    $this->actingAs($this->admin)
        ->post(route('admin.categories.reorder'), ['ids' => [$second->id, $first->id]])
        ->assertRedirect();

    expect($second->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1);
});
