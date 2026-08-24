<?php

use App\Models\Product;

it('redirects legacy public URLs with 301', function (string $from, string $to) {
    $this->get($from)->assertMovedPermanently()->assertRedirect($to);
})->with([
    'historia' => ['/historia', '/nossa-historia'],
    'estatuas' => ['/estatuas', '/produtos'],
    'lancamentos' => ['/lancamentos', '/produtos'],
    'informacoes' => ['/informacoes', '/contato'],
    'orcamentos' => ['/orcamentos', '/meus-orcamentos'],
]);

it('redirects legacy product and category URLs preserving the slug', function () {
    $product = Product::factory()->published()->create(['slug' => 'buda-legado']);

    $this->get('/estatua/buda-legado/visualizar')
        ->assertMovedPermanently()
        ->assertRedirect(route('products.show', $product));

    $this->get('/estatuas/budas/categories')
        ->assertMovedPermanently()
        ->assertRedirect('/produtos?categoria=budas');
});
