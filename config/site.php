<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tema padrão do site
    |--------------------------------------------------------------------------
    |
    | Usado quando o visitante não tem cookie de tema e não existe o setting
    | `site_default_theme` no banco. Valores válidos: App\Enums\SiteTheme.
    |
    */

    'default_theme' => env('SITE_DEFAULT_THEME', 'stone'),

    /*
    |--------------------------------------------------------------------------
    | Senha inicial do admin
    |--------------------------------------------------------------------------
    |
    | Usada pelo AdminUserSeeder ao criar a conta administrativa inicial.
    | Se ausente, uma senha aleatória é gerada e exibida no console.
    |
    */

    'admin_initial_password' => env('ADMIN_INITIAL_PASSWORD'),

];
