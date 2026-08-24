<?php

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\SettingsSeeder;

test('the settings seeder stores the company data', function () {
    $this->seed(SettingsSeeder::class);

    expect(Setting::get('company_name'))->toBe('Estátuas Oliveira')
        ->and(Setting::get('contact_email'))->toBe('contato@estatuasoliveira.com.br')
        ->and(Setting::get('contact_whatsapp'))->toBe('5551999732078')
        ->and(Setting::get('address_city'))->toBe('Osório');
});

test('the admin user seeder creates a single admin account', function () {
    $this->seed(AdminUserSeeder::class);
    $this->seed(AdminUserSeeder::class);

    $admins = User::query()->where('email', 'contato@estatuasoliveira.com.br')->get();

    expect($admins)->toHaveCount(1)
        ->and($admins->first()->role)->toBe(UserRole::Admin)
        ->and($admins->first()->isAdmin())->toBeTrue();
});
