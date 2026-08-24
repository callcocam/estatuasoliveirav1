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

];
