<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the initial administrator account.
     */
    public function run(): void
    {
        $email = 'contato@estatuasoliveira.com.br';

        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        $configured = config('site.admin_initial_password');
        $password = is_string($configured) && $configured !== '' ? $configured : Str::password(16);

        User::factory()->create([
            'name' => 'Estátuas Oliveira',
            'email' => $email,
            'role' => UserRole::Admin,
            'password' => $password,
        ]);

        if ($password !== $configured) {
            $this->command->warn("Admin criado ({$email}) com senha gerada: {$password}");
        }
    }
}
