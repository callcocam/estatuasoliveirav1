<?php

use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the home page with published content', function () {
    Slider::factory()->published()->create(['title' => 'Slide publicado']);
    Slider::factory()->create(['status' => 'draft', 'title' => 'Slide rascunho']);

    $category = Category::factory()->published()->create();
    Product::factory()->published()->featured()->for($category)->create(['name' => 'Buda destaque']);
    Product::factory()->featured()->create(['status' => 'draft', 'name' => 'Rascunho oculto']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/Home')
            ->has('sliders', 1)
            ->where('sliders.0.title', 'Slide publicado')
            ->has('featuredProducts', 1)
            ->where('featuredProducts.0.name', 'Buda destaque')
            ->has('categories', 1));
});

it('exposes one slide per slider image so the hero rotates', function () {
    $slider = Slider::factory()->published()->create(['title' => 'Estátuas Oliveira']);

    $slider->media()->saveMany(Media::factory()->count(3)->make());

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('sliders', 3)
            ->where('sliders.0.title', 'Estátuas Oliveira')
            ->where('sliders.2.title', 'Estátuas Oliveira')
            ->whereNot('sliders.0.image', null));
});

it('shares the company profile from settings', function () {
    Setting::set('company_name', 'Estátuas Oliveira Teste');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.name', 'Estátuas Oliveira Teste'));
});

it('renders the browser tab favicon links', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<link rel="icon" href="/favicon.ico" sizes="48x48">', false)
        ->assertSee('<link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">', false)
        ->assertSee('<link rel="icon" href="/favicon-16x16.png" type="image/png" sizes="16x16">', false)
        ->assertSee('<link rel="apple-touch-icon" href="/apple-touch-icon.png">', false);
});

it('uses the branding icon uploaded in the settings across the system', function () {
    Setting::set('branding_logo_path', 'branding/icon.png', 'branding');
    $iconUrl = Storage::disk('public')->url('branding/icon.png');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<link rel="icon" href="'.$iconUrl.'">', false)
        ->assertSee('<link rel="apple-touch-icon" href="'.$iconUrl.'">', false)
        ->assertInertia(fn (Assert $page) => $page->where('site.logoUrl', $iconUrl));
});

it('falls back to the static logo when no branding icon is uploaded', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('site.logoUrl', '/images/logo.png'));
});
