<?php

it('renders site pages without the dark class by default', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('class="dark"', false);
});

it('applies the dark class when the visitor cookie asks for dark', function () {
    $this->withUnencryptedCookie('appearance', 'dark')
        ->get('/')
        ->assertOk()
        ->assertSee('class="dark"', false);
});

it('keeps the dark class independent from the color theme cookie', function () {
    $this->withUnencryptedCookie('appearance', 'dark')
        ->withUnencryptedCookie('site_theme', 'terracotta')
        ->get('/')
        ->assertOk()
        ->assertSee('data-theme="terracotta"', false)
        ->assertSee('class="dark"', false);
});

it('does not apply the dark class for the light preference', function () {
    $this->withUnencryptedCookie('appearance', 'light')
        ->get('/')
        ->assertOk()
        ->assertDontSee('class="dark"', false);
});
