<?php

use App\Http\Controllers\Site\SitemapController;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the about page', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('site/About'));
});

it('has a dedicated craft card text distinct from the intro text', function () {
    app()->setLocale('pt_BR');

    expect(__('app.site.about.craft_card_text'))->not->toBe('app.site.about.craft_card_text')
        ->and(__('app.site.about.craft_card_text'))->not->toBe(__('app.site.about.craft_text'));
});

it('renders the terms page with settings content', function () {
    Setting::set('content_terms', 'Conteúdo dos termos.');

    $this->get(route('terms'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/Terms')
            ->where('content', 'Conteúdo dos termos.'));
});

it('renders the public 404 page in the site theme', function () {
    $this->get('/pagina-que-nao-existe')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/Error')
            ->where('status', 404));
});

it('generates a sitemap with static routes and published products', function () {
    $published = Product::factory()->published()->create(['slug' => 'buda-016']);
    Product::factory()->create(['status' => 'draft', 'slug' => 'rascunho-oculto']);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee(route('home'), false)
        ->assertSee(route('products.show', $published), false)
        ->assertDontSee('rascunho-oculto', false);
});

it('caches the generated sitemap', function () {
    Product::factory()->published()->create(['slug' => 'buda-cache']);

    $this->get(route('sitemap'))->assertOk();

    expect(Cache::has(SitemapController::CACHE_KEY))->toBeTrue();

    Product::factory()->published()->create(['slug' => 'nao-aparece-ainda']);

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee('buda-cache', false)
        ->assertDontSee('nao-aparece-ainda', false);
});
