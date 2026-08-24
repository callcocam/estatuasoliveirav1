<?php

use App\Http\Controllers\Customer\QuoteController;
use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\GalleryController;
use App\Http\Controllers\Site\HomeController;
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
Route::post('contato', [ContactController::class, 'store'])->name('contact.store');
Route::get('termos-e-politica', TermsController::class)->name('terms');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn (Request $request) => redirect(RoleRedirect::pathFor($request->user())))->name('dashboard');

    Route::get('meus-orcamentos', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('meus-orcamentos/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
});

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
