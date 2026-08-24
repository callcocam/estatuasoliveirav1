<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', EnsureUserIsAdmin::class])
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::post('categories/{category}/restore', [CategoryController::class, 'restore'])
            ->withTrashed()
            ->name('categories.restore');
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::post('products/{product}/restore', [ProductController::class, 'restore'])
            ->withTrashed()
            ->name('products.restore');
        Route::resource('products', ProductController::class)->except(['show']);

        Route::post('sliders/reorder', [SliderController::class, 'reorder'])->name('sliders.reorder');
        Route::post('sliders/{slider}/restore', [SliderController::class, 'restore'])
            ->withTrashed()
            ->name('sliders.restore');
        Route::resource('sliders', SliderController::class)->except(['show']);

        Route::patch('quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.status');
        Route::post('quotes/{quote}/restore', [QuoteController::class, 'restore'])
            ->withTrashed()
            ->name('quotes.restore');
        Route::resource('quotes', QuoteController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

        Route::patch('messages/{message}/read', [ContactMessageController::class, 'toggleRead'])->name('messages.read');
        Route::post('messages/{message}/restore', [ContactMessageController::class, 'restore'])
            ->withTrashed()
            ->name('messages.restore');
        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::post('media/reorder', [MediaController::class, 'reorder'])->name('media.reorder');
        Route::patch('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::middleware(EnsureUserIsAdmin::class.':admin')->group(function () {
            Route::post('users/{user}/reset-link', [UserController::class, 'sendResetLink'])->name('users.reset-link');
            Route::post('users/{user}/restore', [UserController::class, 'restore'])
                ->withTrashed()
                ->name('users.restore');
            Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

            Route::get('settings', [SiteSettingsController::class, 'edit'])->name('settings.edit');
            Route::post('settings', [SiteSettingsController::class, 'update'])->name('settings.update');
        });
    });
