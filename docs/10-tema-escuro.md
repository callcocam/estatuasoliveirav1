# 10 — Tema Escuro do Site

**Pré-requisitos**: plano 03 concluído. Leia `docs/00-contexto.md`. Comandos via `docker compose exec -T php ...`.

## Objetivo

O site tem dois temas de **cor** (Serene Stone / Terracotta Earth via `[data-theme=...]`), mas ambos só em modo claro. Criar o modo escuro do site: cada tema ganha uma variante dark, com toggle claro/escuro/sistema para o visitante — independente da escolha stone/terracotta.

## Contexto do código

- Paletas do site: `resources/css/themes.css` (`[data-theme='stone']` e `[data-theme='terracotta']` definem `--site-*`). Mapeamento para utilities em `resources/css/app.css` (`--color-site-*`). Regra do plano 03: **somente utilities `site-*` colorem o site** — o dark mode do site deve nascer trocando os valores das variáveis `--site-*`, sem espalhar `dark:` em templates do site.
- Já existe infra de dark do starter (admin/shadcn): `@custom-variant dark`, bloco `.dark { ... }` em `app.css`, `useAppearance.ts` (light/dark/system, classe `.dark` no `<html>`, persistência + script anti-flash — confira `resources/views/app.blade.php`) e página `settings/Appearance.vue`. **Reaproveitar** `useAppearance`; não criar mecanismo paralelo.
- Troca de tema de cor: `resources/js/components/site/ThemeSwitcher.vue` + `useTheme.ts` (cookie lido por `SiteTheme::resolve`).
- Seletor CSS: `data-theme` e `.dark` ficam ambos no `<html>`, então as variantes escuras são `.dark[data-theme='stone'] { --site-...: ...; }` e `.dark[data-theme='terracotta'] { ... }`.

## Passos

1. **Paletas**: derivar versões escuras dos dois temas seguindo a mesma semântica Material-like (surface-dim ⇢ containers ⇢ highest; `on-*` com contraste AA; `inverse-*` invertidos). Documentar os hex escolhidos no próprio `themes.css` (comentário) e conferir contraste dos pares principais (primary/on-primary, surface/on-surface).
2. **Toggle no site**: adicionar controle claro/escuro/sistema no `SiteHeader` (ícone sol/lua, e no menu mobile) usando `useAppearance`. O `ThemeSwitcher` de cores continua separado — são dois eixos (cor × luminosidade).
3. **Anti-flash/SSR**: garantir que o estado inicial do `.dark` é aplicado antes do paint também nas páginas do site (script inline no `app.blade.php` — provavelmente já cobre; validar).
4. **Varredura visual**: Home, Produtos (index/show), Galeria (lightbox), Contato, Termos, História + páginas de auth (plano 09) nos 4 combos (2 temas × claro/escuro). Atenção a imagens sobre fundo escuro, sombras e `--site-surface-tint`.
5. **Imagens/OG**: hero e placeholders devem funcionar no escuro (usar overlay, não trocar assets).
6. Testes: unit/feature onde couber (ex.: cookie/appearance compartilhado, componente do toggle); o grosso é CSS — manter suíte verde e `npm run lint` limpo. Se algum valor for exposto via Inertia, cobrir com `assertInertia`.

## Encerramento

Pint (se tocar PHP) + `php artisan test --compact` + `npm run build && npm run lint`. Registrar via `record-rule` (glob `resources/css/**`) a convenção `.dark[data-theme=...]` para os próximos agentes.

## Registro de execução

Executado em 2026-08-24.

- **Paletas**: blocos `.dark[data-theme='stone']` e `.dark[data-theme='terracotta']` em `resources/css/themes.css`, mesma semântica Material-like (surfaces dim ⇢ highest, `on-*` tone ~80–90, `inverse-*` espelhados, primary claro com on-primary escuro ~9:1). Containers primary/tertiary continuam escuros com `on-*-container` claros (regra existente preservada). `--site-shadow` sobe de 4% para 40% de opacidade no escuro. Hex documentados em comentários no próprio arquivo.
- **Toggle**: novo `resources/js/components/site/SiteAppearanceToggle.vue` (claro/escuro/sistema, ícones sol/lua/monitor, mesmo visual do ThemeSwitcher, prop `compact`), reaproveitando `useAppearance`. Inserido no SiteHeader: topbar (compact, ao lado do ThemeSwitcher) e menu mobile. Eixos cor × luminosidade independentes.
- **Anti-flash**: validado — `app.blade.php` já aplica `.dark` server-side (cookie `appearance` via HandleAppearance, não criptografado) + script inline para `system`; cobre o site sem mudanças.
- **i18n**: chaves `app.theme.appearance.*` em `lang/pt_BR/app/theme.php`.
- **Testes**: `tests/Feature/SiteAppearanceTest.php` (default sem `.dark`, cookie dark aplica, independência do `site_theme`, light não aplica). Suíte completa: 207 passed. `npm run build` + `npm run lint` limpos; Pint ok.
- **Regra registrada**: `.ai/rules/resources-css.md` — convenção `.dark[data-theme=...]`.
- **Pendente humano**: varredura visual dos 4 combos (2 temas × claro/escuro) nas páginas Home/Produtos/Galeria/Contato/Termos/História/auth, e conferência de imagens/hero sobre fundo escuro.
