<?php

use App\Models\Category;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

it('lists only published products', function () {
    Product::factory()->published()->create(['name' => 'Fonte publicada']);
    Product::factory()->create(['status' => 'draft', 'name' => 'Vaso rascunho']);
    Product::factory()->published()->create(['name' => 'Excluído'])->delete();

    $this->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/products/Index')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Fonte publicada'));
});

it('filters products by category slug', function () {
    $budas = Category::factory()->published()->create(['slug' => 'budas']);
    $vasos = Category::factory()->published()->create(['slug' => 'vasos']);

    Product::factory()->published()->for($budas)->create(['name' => 'Buda zen']);
    Product::factory()->published()->for($vasos)->create(['name' => 'Vaso grego']);

    $this->get(route('products.index', ['categoria' => 'budas']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Buda zen')
            ->where('filters.categoria', 'budas'));
});

it('searches products by name or reference', function () {
    Product::factory()->published()->create(['name' => 'Buda tailandês', 'reference' => '016']);
    Product::factory()->published()->create(['name' => 'Fonte cascata', 'reference' => '099']);

    $this->get(route('products.index', ['busca' => 'tailandês']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1));

    $this->get(route('products.index', ['busca' => '099']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Fonte cascata'));
});
