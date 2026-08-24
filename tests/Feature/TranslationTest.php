<?php

use App\Support\Translation\MergingFileLoader;
use Inertia\Testing\AssertableInertia as Assert;

test('the merging file loader is registered as the translation loader', function () {
    expect(app('translation.loader'))->toBeInstanceOf(MergingFileLoader::class);
});

test('dot notation resolves keys from group subdirectory files', function () {
    app()->setLocale('pt_BR');

    expect(__('app.common.actions.save'))->toBe('Salvar')
        ->and(__('app.tagline'))->not->toBe('app.tagline');
});

test('json translations keep working alongside the merging loader', function () {
    app()->setLocale('pt_BR');

    expect(__('Profile updated.'))->toBe('Perfil atualizado.');
});

test('placeholders are interpolated in merged group keys', function () {
    app()->setLocale('pt_BR');

    expect(__('validation.required', ['attribute' => 'nome']))
        ->toBe('O campo nome é obrigatório.');
});

test('whatsapp button labels are translated', function () {
    app()->setLocale('pt_BR');

    expect(__('app.site.whatsapp.button'))->not->toBe('app.site.whatsapp.button')
        ->and(__('app.site.whatsapp.aria_label'))->not->toBe('app.site.whatsapp.aria_label');
});

test('translations and locale are shared as inertia props', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('translations.app')
            ->has('translations.auth')
            ->has('translations.validation')
            ->where('locale', config('app.locale')),
        );
});
