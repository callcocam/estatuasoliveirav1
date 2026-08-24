# 09 — Páginas de Auth com a Cara do Site

**Pré-requisitos**: plano 03 (design system) concluído; ideal rodar após o 08. Leia `docs/00-contexto.md`. Comandos via `docker compose exec -T php ...`.

## Objetivo

As páginas de auth (`resources/js/pages/auth/`: Login, Register, ForgotPassword, ResetPassword, ConfirmPassword, TwoFactorChallenge, VerifyEmail) usam o visual genérico do starter kit. Retematizá-las com a identidade do site (Serene Stone / Terracotta Earth), respeitando o tema ativo do visitante.

## Contexto do código

- Wrapper atual: `resources/js/layouts/AuthLayout.vue` → `resources/js/layouts/auth/AuthSimpleLayout.vue` (existem também `AuthCardLayout`/`AuthSplitLayout`, não usados).
- Design system do site: tokens `site-*` em `resources/css/app.css` + paletas por `[data-theme='stone'|'terracotta']` em `resources/css/themes.css`. Fontes: `font-display` (EB Garamond) para títulos, `font-site` (Manrope) para texto. Referências visuais: `stitch_est_tuas_oliveira_ui_redesign/*/DESIGN.md`.
- O tema é resolvido server-side (`SiteTheme::resolve` em `HandleInertiaRequests`, atributo `data-theme` no `<html>`), então as páginas de auth já herdam a paleta — basta usar utilities `site-*`.
- Logo/nome da empresa: settings de branding (`branding_logo_path`, `company_name`) — ver como `SiteHeader`/`useCompany` consomem.
- **Não** alterar fluxos do Fortify (rotas, requests, responses) — apenas apresentação. As responses de redirect usam `RoleRedirect` (regra `.ai/rules/support.md`).
- Formulários: manter `<Form>`/componentes de erro existentes (skill `inertia-vue-development`). Traduções já existem em `lang/*/app/auth*` — completar o que faltar, sem texto hardcoded.

## Passos

1. Redesenhar `AuthSimpleLayout.vue` (ou criar `auth/AuthSiteLayout.vue` e apontar o wrapper): fundo `bg-site-surface`, card `bg-site-surface-container-lowest` com `border-site-outline-variant`, título em `font-display`, logo da empresa linkando para `route('home')`, link "← Voltar ao site".
2. Ajustar inputs/botões das páginas de auth para as variantes `site-*` (criar, se ainda não houver, componentes reutilizáveis tipo `SiteInput`/`SiteButton` em `resources/js/components/site/` — verifique se já existem antes de criar).
3. Garantir responsivo + estados de erro/sucesso legíveis nos dois temas.
4. Testes: as feature tests de auth existentes (`tests/Feature/Auth/`) devem continuar verdes (são o contrato dos fluxos); adicionar smoke asserts de componente Inertia se algum caminho de página mudar.

## Encerramento

Pint + Larastan + `php artisan test --compact` + `npm run build && npm run lint`. Validar visualmente nos dois temas (`data-theme`) antes de encerrar.

## Registro de execução

_(preencher ao executar)_
