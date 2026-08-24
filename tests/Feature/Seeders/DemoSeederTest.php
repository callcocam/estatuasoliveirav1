<?php

use App\Models\Media;
use App\Models\Product;
use App\Models\Slider;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

test('the demo seeder downloads legacy site images and attaches them as media', function () {
    Storage::fake('public');

    Http::fake([
        'estatuasoliveira.com.br/' => Http::response(<<<'HTML'
            <img src="https://estatuasoliveira.com.br/storage/products/202304/produto-um.jpeg">
            <img src="https://estatuasoliveira.com.br/storage/products/202304/produto-dois.jpeg">
            <img src="https://estatuasoliveira.com.br/storage/sliders/202004/slider-um.jpeg">
            HTML),
        'estatuasoliveira.com.br/estatuas/budas/categories' => Http::response(
            '<a href="https://estatuasoliveira.com.br/storage/products/202304/produto-tres.jpeg">'
        ),
        'estatuasoliveira.com.br/storage/*' => Http::response('fake-image-bytes'),
    ]);

    $this->seed(DemoSeeder::class);

    Storage::disk('public')->assertExists([
        'media/products/produto-um.jpeg',
        'media/products/produto-dois.jpeg',
        'media/products/produto-tres.jpeg',
        'media/sliders/slider-um.jpeg',
    ]);

    $product = Product::query()->with('media')->firstOrFail();

    expect($product->media)->toHaveCount(2)
        ->and($product->coverMedia())->not->toBeNull()
        ->and($product->media->first()->path)->toStartWith('media/products/');

    $slider = Slider::query()->with('media')->firstOrFail();

    expect($slider->media)->toHaveCount(1)
        ->and($slider->media->first()->path)->toStartWith('media/sliders/');

    Media::query()->each(function (Media $media) {
        Storage::disk('public')->assertExists($media->path);
    });
});

test('the demo seeder still seeds products when the legacy site is unreachable', function () {
    Storage::fake('public');

    Http::fake(fn () => Http::response(status: 500));

    $this->seed(DemoSeeder::class);

    expect(Product::query()->published()->count())->toBe(30)
        ->and(Media::query()->count())->toBe(0);
});
