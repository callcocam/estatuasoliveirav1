<?php

namespace App\Enums;

use App\Models\Setting;
use Illuminate\Http\Request;

enum SiteTheme: string
{
    case Stone = 'stone';
    case Terracotta = 'terracotta';

    /**
     * Nome do cookie do visitante (fora da criptografia de cookies —
     * ver bootstrap/app.php — porque é gravado também pelo JavaScript).
     */
    public const COOKIE = 'site_theme';

    /**
     * Chave do setting global com o tema padrão do site.
     */
    public const SETTING_KEY = 'site_default_theme';

    /**
     * Resolve o tema da requisição: cookie do visitante > setting global
     * `site_default_theme` > config('site.default_theme') > Stone.
     */
    public static function resolve(Request $request): self
    {
        return self::tryFrom((string) $request->cookie(self::COOKIE))
            ?? self::tryFrom((string) Setting::get(self::SETTING_KEY))
            ?? self::tryFrom((string) config('site.default_theme'))
            ?? self::Stone;
    }

    /**
     * Get the human-readable label for the theme.
     */
    public function label(): string
    {
        return match ($this) {
            self::Stone => 'Pedra & Prata',
            self::Terracotta => 'Terracota',
        };
    }
}
