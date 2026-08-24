<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Support\Str;

test('products use ulid primary keys', function () {
    $product = Product::factory()->create();

    expect(Str::isUlid($product->id))->toBeTrue()
        ->and(Str::isUlid($product->category_id))->toBeTrue();
});

test('a product belongs to a category', function () {
    $category = Category::factory()->published()->create();
    $product = Product::factory()->for($category)->create();

    expect($product->category->is($category))->toBeTrue()
        ->and($category->products->first()->is($product))->toBeTrue();
});

test('a product can have media attached', function () {
    $product = Product::factory()->create();
    Media::factory()->count(2)->for($product, 'mediable')->create();

    expect($product->media)->toHaveCount(2)
        ->and($product->coverMedia())->not->toBeNull();
});

test('the published scope only returns published products', function () {
    Product::factory()->create();
    $published = Product::factory()->published()->create();

    expect(Product::query()->published()->get())
        ->toHaveCount(1)
        ->first()->id->toBe($published->id)
        ->and($published->status)->toBe(PublishStatus::Published);
});

test('the featured scope only returns featured products', function () {
    Product::factory()->published()->create();
    $featured = Product::factory()->published()->featured()->create();

    expect(Product::query()->featured()->get())
        ->toHaveCount(1)
        ->first()->id->toBe($featured->id);
});

test('deleting a category keeps its products without category', function () {
    $product = Product::factory()->create();

    $product->category->forceDelete();

    expect($product->fresh()->category_id)->toBeNull();
});

test('products are soft deleted', function () {
    $product = Product::factory()->create();

    $product->delete();

    expect(Product::query()->find($product->id))->toBeNull()
        ->and(Product::withTrashed()->find($product->id))->not->toBeNull();
});
