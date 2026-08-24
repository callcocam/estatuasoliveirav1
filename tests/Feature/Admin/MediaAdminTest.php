<?php

use App\Models\Product;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('uploads an image to a product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg', 800, 600),
            'mediable_type' => 'product',
            'mediable_id' => $product->id,
        ])
        ->assertRedirect();

    $media = $product->media()->first();

    expect($media)->not->toBeNull()
        ->and($media->path)->toStartWith("products/{$product->id}/")
        ->and($media->custom_properties['width'])->toBe(800)
        ->and($media->custom_properties['height'])->toBe(600);

    Storage::disk('public')->assertExists($media->path);
});

test('uploads an image to a slider', function () {
    $slider = Slider::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('slide.png'),
            'mediable_type' => 'slider',
            'mediable_id' => $slider->id,
        ])
        ->assertRedirect();

    expect($slider->media()->count())->toBe(1);
});

test('rejects invalid uploads', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),
            'mediable_type' => 'product',
            'mediable_id' => $product->id,
        ])
        ->assertSessionHasErrors('file');

    $this->actingAs($this->admin)
        ->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('foto.jpg'),
            'mediable_type' => 'user',
            'mediable_id' => $product->id,
        ])
        ->assertSessionHasErrors('mediable_type');
});

test('reorders media', function () {
    $product = Product::factory()->create();

    $first = $product->media()->create([
        'collection' => 'default', 'disk' => 'public', 'path' => 'a.jpg',
        'file_name' => 'a.jpg', 'mime_type' => 'image/jpeg', 'size' => 1, 'sort_order' => 0,
    ]);
    $second = $product->media()->create([
        'collection' => 'default', 'disk' => 'public', 'path' => 'b.jpg',
        'file_name' => 'b.jpg', 'mime_type' => 'image/jpeg', 'size' => 1, 'sort_order' => 1,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.media.reorder'), ['ids' => [$second->id, $first->id]])
        ->assertRedirect();

    expect($second->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1);
});

test('updates the alt text and deletes media with its file', function () {
    $product = Product::factory()->create();

    Storage::disk('public')->put('products/x/foto.jpg', 'conteudo');

    $media = $product->media()->create([
        'collection' => 'default', 'disk' => 'public', 'path' => 'products/x/foto.jpg',
        'file_name' => 'foto.jpg', 'mime_type' => 'image/jpeg', 'size' => 1, 'sort_order' => 0,
    ]);

    $this->actingAs($this->admin)->patch(route('admin.media.update', $media), ['alt' => 'Estátua de Buda']);
    expect($media->refresh()->custom_properties['alt'])->toBe('Estátua de Buda');

    $this->actingAs($this->admin)->delete(route('admin.media.destroy', $media));
    expect($product->media()->count())->toBe(0);
    Storage::disk('public')->assertMissing('products/x/foto.jpg');
});
