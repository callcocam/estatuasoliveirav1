<?php

use App\Http\Controllers\Customer\QuoteController;
use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\GalleryController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PrivacyController;
use App\Http\Controllers\Site\ProductController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\TermsController;
use App\Support\RoleRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('nossa-historia', AboutController::class)->name('about');
Route::get('produtos', [ProductController::class, 'index'])->name('products.index');
Route::get('produtos/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('galeria', GalleryController::class)->name('gallery');
Route::get('contato', [ContactController::class, 'show'])->name('contact');
Route::post('contato', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('termos-de-uso', TermsController::class)->name('terms');
Route::get('politica-de-privacidade', PrivacyController::class)->name('privacy');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

// Redirects 301 das URLs do site legado (estatuasoliveira.com.br).
Route::redirect('termos-e-politica', '/termos-de-uso', 301);
Route::redirect('historia', '/nossa-historia', 301);
Route::redirect('estatuas', '/produtos', 301);
Route::redirect('lancamentos', '/produtos', 301);
Route::redirect('informacoes', '/contato', 301);
Route::get('estatua/{slug}/visualizar', fn (string $slug) => redirect()->route('products.show', $slug, 301));
Route::get('estatuas/{slug}/categories', fn (string $slug) => redirect()->to('/produtos?categoria='.$slug, 301));
Route::redirect('orcamentos', '/meus-orcamentos', 301);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn (Request $request) => redirect(RoleRedirect::pathFor($request->user())))->name('dashboard');

    Route::get('meus-orcamentos', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('meus-orcamentos/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
});

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
