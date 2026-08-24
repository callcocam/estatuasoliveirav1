# 11 — Área do Cliente (não-admin)

**Pré-requisitos**: planos 05 e 08 concluídos. Leia `docs/00-contexto.md`, `.ai/rules/support.md` e `.ai/rules/fortify-models.md`. Comandos via `docker compose exec -T php ...`.

## Objetivo

Revisar a experiência do usuário **Customer** (não-admin): hoje ele cai numa área com `AppLayout` (sidebar do starter kit) contendo "Meus orçamentos" e as páginas de settings. Garantir que:

1. Ele **nunca** acessa telas/rotas de admin (permissão reduzida, só orçamentos + perfil).
2. A área dele é enxuta e com identidade própria (sem sobras do starter kit).

## Contexto do código

- Rotas do cliente (`routes/web.php`, grupo `auth`+`verified`): `quotes.index`/`quotes.show` (`/meus-orcamentos`, `App\Http\Controllers\Customer\QuoteController`) + `routes/settings.php` (Profile/Security/Appearance em `resources/js/pages/settings/`).
- Admin é protegido por `EnsureUserIsAdmin` em `routes/admin.php` (grupo com `['auth', 'verified', EnsureUserIsAdmin::class]` + um subgrupo `EnsureUserIsAdmin::class.':admin'`). O sidebar do admin é `resources/js/components/admin/AdminSidebar.vue` (não confundir com o do cliente).
- Sidebar do cliente: `resources/js/components/AppSidebar.vue` via `AppLayout` → `layouts/app/AppSidebarLayout.vue`. Hoje o `footerNavItems` ainda aponta para `github.com/laravel/vue-starter-kit` e docs do Laravel — **remover**; o logo usa `AppLogo` genérico.
- Redirect por papel: `App\Support\RoleRedirect::pathFor()` — Customer → `quotes.index`. Não hardcodar caminhos (regra `support.md`).
- `Quote` pertence ao usuário; conferir que `Customer\QuoteController` filtra por `$request->user()` e que `show` de orçamento alheio dá 403/404 (deve existir teste; completar se faltar).

## Passos

1. **Auditoria de acesso** (testes primeiro): matriz Customer × rotas admin (dashboard, produtos, categorias, sliders, mídias, orçamentos admin, mensagens, usuários, configurações) → tudo 403; Manager × subgrupo `:admin` (usuários/configurações) → 403; convidado → redirect login. Verificar o que já existe em `tests/Feature/` antes de escrever (ex.: `EnsureUserIsAdmin` tests) — não duplicar.
2. **Escopo de dados**: teste de que Customer só lista/vê os próprios orçamentos (`quotes.show` alheio → 404).
3. **Sidebar do cliente** (`AppSidebar.vue`): itens = Meus orçamentos, Perfil, Segurança, Aparência + link "Voltar ao site" (`route('home')`); remover `footerNavItems` do starter; logo/nome da empresa via settings de branding (como o admin faz). `NavUser` mantém logout.
4. **Identidade visual**: aproximar a área do cliente dos tokens do site onde for barato (fundo, tipografia de títulos) — sem reescrever os componentes shadcn; o objetivo é não parecer "outro produto". Deve respeitar claro/escuro (plano 10).
5. **Settings**: confirmar que as três páginas de settings funcionam para Customer (são compartilhadas com admin — se o layout divergir por papel, tratar via props/slots, não duplicar página).
6. Traduções pt_BR/en para itens novos de navegação.

## Encerramento

Pint + Larastan + `php artisan test --compact` + `npm run build && npm run lint`. Registrar via `record-rule` a separação `AppSidebar` (cliente) × `AdminSidebar` (admin) e a matriz de permissão coberta por testes.

## Registro de execução

**Executado em 2026-08-24.**

1. **Auditoria de acesso** — `tests/Feature/Admin/AdminAccessTest.php` ampliado com dataset `admin get routes` (dashboard, categorias, produtos, sliders, orçamentos, mensagens, usuários, configurações): Customer → 403 em todas; convidado → redirect login em todas; + `admin.media.store` (POST) → 403 p/ Customer. Manager × subgrupo `:admin` já estava coberto (mantido, sem duplicar).
2. **Escopo de dados** — já integralmente coberto por `tests/Feature/Quotes/CustomerQuotesTest.php` (lista só os próprios, exclui soft-deleted, `quotes.show` alheio → 404, unverified → verification.notice). Controller confere `user_id` com `abort_unless(..., 404)`. Nada a adicionar.
3. **Sidebar do cliente** — `AppSidebar.vue` reescrito: itens Meus orçamentos (Customer) ou Painel (admin/manager, já que settings é compartilhado), Perfil, Segurança, Aparência (reusa chaves `app.settings.layout.nav.*`); footer com "Voltar ao site" (`home()`) + `NavUser` (logout). Removidos `footerNavItems` do starter e os arquivos órfãos `NavFooter.vue`, `AppHeader.vue`, `AppHeaderLayout.vue`.
4. **Identidade visual** — `AppLogo.vue` agora usa `/images/logo.png` + nome da empresa via `useCompany()` (prop `site`/settings, como o site); ícone Laravel do starter removido. Títulos das páginas de quotes com `font-display` (EB Garamond). Sem mudanças de cor — claro/escuro preservado.
5. **Settings** — as 3 páginas compartilham `AppLayout`+`SettingsLayout` para todos os papéis; divergência tratada no próprio `AppSidebar` role-aware (sem duplicar página). Testes existentes de Profile/Security/Appearance passam.
6. **Traduções** — `lang/pt_BR/app/nav.php`: removidas `repository`/`documentation` (starter), adicionadas `admin_panel` e `back_to_site`. O projeto mantém apenas `lang/pt_BR` (fallback `en` é só do framework — convenção existente).

Encerramento: Pint ✓, Larastan ✓ (0 erros), `php artisan test --compact` ✓ (222 passed), `npm run build` ✓, `npm run lint` ✓. Regra registrada em `.ai/rules/feature-admin.md` (separação AppSidebar × AdminSidebar + matriz de acesso).
