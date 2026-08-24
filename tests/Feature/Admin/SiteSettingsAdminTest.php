<?php

use App\Enums\SiteTheme;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('shows the settings form with current values', function () {
    Setting::set('company_name', 'Estátuas Oliveira', 'company');

    $this->actingAs($this->admin)
        ->get(route('admin.settings.edit'))
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/Site')
            ->where('values.company_name', 'Estátuas Oliveira')
            ->has('themes', 2));
});

test('saves settings including the default site theme', function () {
    $this->actingAs($this->admin)->post(route('admin.settings.update'), [
        'company_name' => 'Estátuas Oliveira',
        'contact_email' => 'contato@estatuasoliveira.com.br',
        'contact_whatsapp' => '5546999990000',
        'site_default_theme' => 'terracotta',
    ]);

    expect(Setting::get('company_name'))->toBe('Estátuas Oliveira')
        ->and(Setting::get('contact_whatsapp'))->toBe('5546999990000')
        ->and(Setting::get(SiteTheme::SETTING_KEY))->toBe('terracotta');
});

test('validates settings payload', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.settings.update'), [
            'company_name' => '',
            'contact_email' => 'not-an-email',
            'site_default_theme' => 'neon',
        ])
        ->assertSessionHasErrors(['company_name', 'contact_email', 'site_default_theme']);
});

test('uploads a logo replacing the previous file', function () {
    Storage::fake('public');

    Storage::disk('public')->put('branding/antigo.png', 'x');
    Setting::set('branding_logo_path', 'branding/antigo.png', 'branding');

    $this->actingAs($this->admin)->post(route('admin.settings.update'), [
        'company_name' => 'Estátuas Oliveira',
        'site_default_theme' => 'stone',
        'logo' => UploadedFile::fake()->image('logo.png'),
    ]);

    $newPath = Setting::get('branding_logo_path');

    expect($newPath)->not->toBe('branding/antigo.png');
    Storage::disk('public')->assertExists($newPath);
    Storage::disk('public')->assertMissing('branding/antigo.png');
});
