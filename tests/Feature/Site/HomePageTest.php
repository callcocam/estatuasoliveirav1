<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Slider;
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

it('shares the company profile from settings', function () {
    Setting::set('company_name', 'Estátuas Oliveira Teste');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.name', 'Estátuas Oliveira Teste'));
});
