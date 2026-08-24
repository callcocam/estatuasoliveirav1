---
paths:
  - 'resources/js/**,app/Http/Middleware/**,resources/css/**'
---

# Css

## Tema do site público (stone/terracotta)
O tema visual do site é resolvido por App\Enums\SiteTheme: cookie `site_theme` (não criptografado, gravado pelo JS) > setting `site_default_theme` > config('site.default_theme') > stone. HandleSiteTheme aplica `data-theme` no <html> via Blade (sem flash); HandleInertiaRequests compartilha a prop `theme`. No frontend use o composable useTheme() (resources/js/composables/useTheme.ts) e o componente site/ThemeSwitcher.vue — nunca manipule o cookie/data-theme direto. Tokens por tema vivem em resources/css/themes.css sob [data-theme="..."].

## Pares container/on-container em themes.css
Os `--site-primary-container` e `--site-tertiary-container` são ESCUROS nos dois temas; o par `on-*-container` deve ser claro (tom ~90: #d8e2ff/#ffdbcf no stone, #ffdbd0/#ffddb8 no terracotta) — não usar tons médios apagados, o contraste quebra. `secondary-container` é claro com "on" escuro. Botões sólidos dentro desses cards escuros usam `bg-site-surface text-site-primary` (botão claro), nunca `bg-site-primary` (some no fundo escuro).
