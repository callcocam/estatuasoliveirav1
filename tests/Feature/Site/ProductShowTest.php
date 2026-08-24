<?php

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

it('shows a published product with its media', function () {
    $category = Category::factory()->published()->create();
    $product = Product::factory()->published()->for($category)->create(['slug' => 'buda-016']);
    Media::factory()->count(2)->for($product, 'mediable')->create();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/products/Show')
            ->where('product.slug', 'buda-016')
            ->where('product.url', route('products.show', $product))
            ->has('product.images', 2));
});

it('loads related products from the same category as a deferred prop', function () {
    $category = Category::factory()->published()->create();
    $product = Product::factory()->published()->for($category)->create();
    Product::factory()->published()->for($category)->create(['name' => 'Peça irmã']);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('relatedProducts')
            ->loadDeferredProps(fn (Assert $reloaded) => $reloaded
                ->has('relatedProducts', 1)
                ->where('relatedProducts.0.name', 'Peça irmã')));
});

it('returns 404 for draft and deleted products', function () {
    $draft = Product::factory()->create(['status' => 'draft']);
    $this->get(route('products.show', $draft))->assertNotFound();

    $deleted = Product::factory()->published()->create();
    $slug = $deleted->slug;
    $deleted->delete();
    $this->get("/produtos/{$slug}")->assertNotFound();
});
