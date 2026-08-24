<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\GalleryController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ProductController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Controllers\Site\TermsController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
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

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
