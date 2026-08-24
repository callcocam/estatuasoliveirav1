<?php

use App\Models\Media;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('renames product media files and records to the product slug', function () {
    $product = Product::factory()->create(['slug' => 'estatua-de-marmore']);
    $media = Media::factory()->for($product, 'mediable')->create([
        'path' => 'products/202304/1900483027-legado.jpeg',
        'file_name' => '1900483027-legado.jpeg',
    ]);
    Storage::disk('public')->put('products/202304/1900483027-legado.jpeg', 'img');

    $this->artisan('media:rename-to-slug')->assertSuccessful();

    $media->refresh();
    expect($media->path)->toBe('products/estatua-de-marmore.jpeg')
        ->and($media->file_name)->toBe('estatua-de-marmore.jpeg');
    Storage::disk('public')->assertExists('products/estatua-de-marmore.jpeg');
    Storage::disk('public')->assertMissing('products/202304/1900483027-legado.jpeg');
});

test('numbers extra images by sort order', function () {
    $product = Product::factory()->create(['slug' => 'anjo-de-granito']);
    $second = Media::factory()->for($product, 'mediable')->create(['path' => 'products/b.jpg', 'file_name' => 'b.jpg', 'sort_order' => 2]);
    $first = Media::factory()->for($product, 'mediable')->create(['path' => 'products/a.png', 'file_name' => 'a.png', 'sort_order' => 1]);
    Storage::disk('public')->put('products/a.png', 'a');
    Storage::disk('public')->put('products/b.jpg', 'b');

    $this->artisan('media:rename-to-slug')->assertSuccessful();

    expect($first->refresh()->path)->toBe('products/anjo-de-granito.png')
        ->and($second->refresh()->path)->toBe('products/anjo-de-granito-2.jpg');
    Storage::disk('public')->assertExists('products/anjo-de-granito.png');
    Storage::disk('public')->assertExists('products/anjo-de-granito-2.jpg');
});

test('updates the record even when the physical file is missing', function () {
    $product = Product::factory()->create(['slug' => 'fonte-de-pedra']);
    $media = Media::factory()->for($product, 'mediable')->create(['path' => 'products/sumiu.jpg', 'file_name' => 'sumiu.jpg']);

    $this->artisan('media:rename-to-slug')->assertSuccessful();

    expect($media->refresh()->path)->toBe('products/fonte-de-pedra.jpg');
});

test('is idempotent and skips already renamed media', function () {
    $product = Product::factory()->create(['slug' => 'busto-classico']);
    $media = Media::factory()->for($product, 'mediable')->create(['path' => 'products/busto-classico.jpg', 'file_name' => 'busto-classico.jpg']);
    Storage::disk('public')->put('products/busto-classico.jpg', 'img');

    $this->artisan('media:rename-to-slug')->assertSuccessful();

    expect($media->refresh()->path)->toBe('products/busto-classico.jpg');
    Storage::disk('public')->assertExists('products/busto-classico.jpg');
});

test('dry run does not change files or records', function () {
    $product = Product::factory()->create(['slug' => 'leao-de-pedra']);
    $media = Media::factory()->for($product, 'mediable')->create(['path' => 'products/old.jpg', 'file_name' => 'old.jpg']);
    Storage::disk('public')->put('products/old.jpg', 'img');

    $this->artisan('media:rename-to-slug', ['--dry-run' => true])->assertSuccessful();

    expect($media->refresh()->path)->toBe('products/old.jpg');
    Storage::disk('public')->assertExists('products/old.jpg');
});
