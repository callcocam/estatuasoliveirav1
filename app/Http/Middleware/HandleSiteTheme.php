<?php

namespace App\Http\Middleware;

use App\Enums\SiteTheme;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleSiteTheme
{
    /**
     * Compartilha o tema do site com o Blade root (atributo data-theme
     * aplicado server-side para não piscar o tema errado no reload).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        View::share('siteTheme', SiteTheme::resolve($request)->value);

        return $next($request);
    }
}
