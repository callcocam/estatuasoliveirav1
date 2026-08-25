<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('lists products with filters as a deferred prop', function () {
    $category = Category::factory()->create();
    Product::factory()->published()->create(['name' => 'Buda zen', 'category_id' => $category->id]);
    Product::factory()->create(['name' => 'Vaso grego', 'status' => PublishStatus::Draft]);

    $this->actingAs($this->admin)
        ->get(route('admin.products.index', ['status' => 'published']))
        ->assertInertia(fn ($page) => $page
            ->component('admin/products/Index')
            ->missing('products')
            ->where('can.create', true)
            ->loadDeferredProps(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Buda zen')));
});

test('filters trashed products with the trashed param', function () {
    Product::factory()->create(['name' => 'Ativo']);
    Product::factory()->trashed()->create(['name' => 'Na lixeira']);

    $this->actingAs($this->admin)
        ->get(route('admin.products.index', ['trashed' => 'only']))
        ->assertInertia(fn ($page) => $page
            ->loadDeferredProps(fn ($page) => $page
                ->has('products.data', 1)
                ->where('products.data.0.name', 'Na lixeira')
                ->where('products.data.0.deleted', true)));
});

test('respects the per_page whitelist', function () {
    Product::factory()->count(12)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.products.index', ['per_page' => 10]))
        ->assertInertia(fn ($page) => $page
            ->loadDeferredProps(fn ($page) => $page->has('products.data', 10)));

    $this->actingAs($this->admin)
        ->get(route('admin.products.index', ['per_page' => 999]))
        ->assertInertia(fn ($page) => $page
            ->loadDeferredProps(fn ($page) => $page->has('products.data', 12)));
});

test('creates a product and redirects to the edit page', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Fonte cascata',
        'slug' => '',
        'status' => 'draft',
        'featured' => false,
    ]);

    $product = Product::query()->where('name', 'Fonte cascata')->firstOrFail();

    $response->assertRedirect(route('admin.products.edit', $product));
    expect($product->slug)->toBe('fonte-cascata');
});

test('validates the product payload', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            'name' => '',
            'status' => 'invalid',
            'price' => -1,
        ])
        ->assertSessionHasErrors(['name', 'status', 'price']);
});

test('updates a product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $product), [
            'category_id' => null,
            'name' => 'Nome novo',
            'slug' => $product->slug,
            'status' => 'published',
            'featured' => true,
            'stock' => 3,
        ])
        ->assertRedirect();

    expect($product->refresh())
        ->name->toBe('Nome novo')
        ->featured->toBeTrue()
        ->stock->toBe(3);
});

test('duplicates a product as a draft copy with its media files', function () {
    Storage::fake('public');

    $product = Product::factory()->published()->create(['name' => 'Buda']);

    $path = UploadedFile::fake()->image('buda.jpg')->store("products/{$product->id}", 'public');
    $product->media()->create([
        'collection' => 'default',
        'disk' => 'public',
        'path' => $path,
        'file_name' => 'buda.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
        'sort_order' => 0,
    ]);

    $this->actingAs($this->admin)->post(route('admin.products.duplicate', $product));

    $copy = Product::query()->where('name', 'Buda (cópia)')->firstOrFail();

    expect($copy->status)->toBe(PublishStatus::Draft)
        ->and($copy->media()->count())->toBe(1);

    Storage::disk('public')->assertExists($copy->media()->first()->path);
});

test('soft deletes and restores a product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.products.destroy', $product));
    expect($product->refresh()->trashed())->toBeTrue();

    $this->actingAs($this->admin)->post(route('admin.products.restore', $product->slug));
    expect($product->refresh()->trashed())->toBeFalse();
});

test('permanently deletes a trashed product with its media files', function () {
    Storage::fake('public');

    $product = Product::factory()->trashed()->create();

    $path = UploadedFile::fake()->image('buda.jpg')->store("products/{$product->id}", 'public');
    $product->media()->create([
        'collection' => 'default',
        'disk' => 'public',
        'path' => $path,
        'file_name' => 'buda.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 100,
        'sort_order' => 0,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.products.destroy', $product->slug))
        ->assertRedirect(route('admin.products.index', ['trashed' => 'only']));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    $this->assertDatabaseMissing('media', ['path' => $path]);
    Storage::disk('public')->assertMissing($path);
});

test('managers may manage products', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get(route('admin.products.index'))
        ->assertInertia(fn ($page) => $page->where('can.delete', true));
});

test('customers cannot manage products', function () {
    $customer = User::factory()->create();
    $product = Product::factory()->create();

    $this->actingAs($customer)->get(route('admin.products.index'))->assertForbidden();
    $this->actingAs($customer)->delete(route('admin.products.destroy', $product))->assertForbidden();
});
