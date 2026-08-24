<?php

use App\Enums\SiteTheme;
use App\Models\Setting;

it('defaults to the stone theme', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('data-theme="stone"', false);
});

it('shares the theme with inertia pages', function () {
    $this->get('/')
        ->assertInertia(fn ($page) => $page->where('theme', 'stone'));
});

it('uses the visitor cookie over the global setting', function () {
    Setting::query()->create([
        'key' => SiteTheme::SETTING_KEY,
        'value' => SiteTheme::Stone->value,
    ]);

    $this->withUnencryptedCookie(SiteTheme::COOKIE, SiteTheme::Terracotta->value)
        ->get('/')
        ->assertOk()
        ->assertSee('data-theme="terracotta"', false)
        ->assertInertia(fn ($page) => $page->where('theme', 'terracotta'));
});

it('falls back to the global setting when there is no cookie', function () {
    Setting::query()->create([
        'key' => SiteTheme::SETTING_KEY,
        'value' => SiteTheme::Terracotta->value,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('data-theme="terracotta"', false);
});

it('ignores invalid cookie values', function () {
    $this->withUnencryptedCookie(SiteTheme::COOKIE, 'neon-pink')
        ->get('/')
        ->assertOk()
        ->assertSee('data-theme="stone"', false);
});
