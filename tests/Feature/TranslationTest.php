<?php

use App\Models\User;
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

    expect(__('Team created.'))->toBe('Equipe criada.');
});

test('placeholders are interpolated in merged group keys', function () {
    app()->setLocale('pt_BR');

    expect(__('validation.required', ['attribute' => 'nome']))
        ->toBe('O campo nome é obrigatório.');
});

test('translations and locale are shared as inertia props', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('translations.app')
            ->has('translations.auth')
            ->has('translations.validation')
            ->where('locale', config('app.locale')),
        );
});
