<?php

use App\Models\Setting;

test('a setting can be stored and retrieved by key', function () {
    Setting::set('contact_email', 'contato@estatuasoliveira.com.br', 'contact');

    expect(Setting::get('contact_email'))->toBe('contato@estatuasoliveira.com.br');
});

test('updating a setting invalidates the cached value', function () {
    Setting::set('contact_phone', '+55 51 99973-2078', 'contact');
    Setting::get('contact_phone');

    Setting::set('contact_phone', '+55 51 00000-0000', 'contact');

    expect(Setting::get('contact_phone'))->toBe('+55 51 00000-0000');
});

test('a missing setting returns the default value', function () {
    expect(Setting::get('missing_key', 'fallback'))->toBe('fallback');
});
