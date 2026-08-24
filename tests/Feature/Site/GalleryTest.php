<?php

use App\Models\Media;
use App\Models\Product;
use Inertia\Testing\AssertableInertia as Assert;

it('shows only media from published products', function () {
    $published = Product::factory()->published()->create(['name' => 'Buda zen']);
    Media::factory()->for($published, 'mediable')->create();

    $draft = Product::factory()->create(['status' => 'draft']);
    Media::factory()->for($draft, 'mediable')->create();

    $this->get(route('gallery'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/Gallery')
            ->has('images.data', 1)
            ->where('images.data.0.productName', 'Buda zen'));
});
