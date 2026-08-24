# 08 — Login/Logout no Site Público

**Pré-requisitos**: planos 04 (site público) e 05 (admin) concluídos. Leia `docs/00-contexto.md` e `docs/README.md` antes. Rode tudo via `docker compose exec -T php ...` (regra `.ai/rules/general.md`).

## Objetivo

O site público hoje não expõe autenticação (o legado tinha "Login / Register" no topo — ver screenshot de referência). Adicionar ao `SiteHeader` (e menu mobile) os pontos de entrada de auth:

- **Visitante**: links "Entrar" (`route('login')`) e "Criar conta" (`route('register')`).
- **Autenticado**: menu do usuário (nome/iniciais) com:
  - "Meus orçamentos" → `route('quotes.index')` (Customer)
  - "Painel" → `route('admin.dashboard')` (apenas Admin/Manager)
  - "Sair" → POST `route('logout')`
- **Rodapé** (`SiteFooter`): link discreto "Área do cliente".

## Contexto do código

- Header: `resources/js/components/site/SiteHeader.vue` (não tem nada de auth hoje). Menu mobile está no mesmo componente.
- `HandleInertiaRequests` já compartilha `auth.user`. **Atenção**: verifique o que o `User` serializa; exponha só o necessário (`name`, `role`) — nunca o model inteiro com campos sensíveis. Se preciso, crie um array explícito em `share()`.
- Papéis: `App\Enums\UserRole` (Admin/Manager/Customer). Redirect pós-login é centralizado em `App\Support\RoleRedirect` — **não** duplicar essa lógica no front; o link "Painel"/"Meus orçamentos" é só navegação (regra `.ai/rules/support.md`).
- Rotas nomeadas Fortify: `login`, `register`, `logout` (POST). Use Wayfinder (`@/routes/...`) — nunca strings hardcoded.
- Logout via Inertia: `<Link :href="logout()" method="post" as="button">` (skill `inertia-vue-development`).
- Estilo: **somente utilities `site-*`** no site (regra em `resources/css/app.css`). Dropdown do usuário pode usar Reka UI/shadcn `DropdownMenu` já existente, retematizado com classes `site-*`.
- Traduções: chaves novas em `lang/pt_BR/app/site.php` (+ espelho `lang/en/app/site.php`). Nunca texto hardcoded (regra `.ai/rules/js.md`).

## Passos

1. Ajustar `HandleInertiaRequests::share()` para expor `auth.user` enxuto (`id`, `name`, `email`, `role`) + teste de que campos sensíveis (password, two_factor_*) não vazam.
2. Criar `resources/js/components/site/SiteUserMenu.vue` (guest links + dropdown autenticado), usar no `SiteHeader` desktop e mobile.
3. Adicionar link "Área do cliente" no `SiteFooter`.
4. Traduções pt_BR/en.
5. Testes Pest (`tests/Feature/Site/`): home renderiza links de login/registro para guest; usuário Customer autenticado vê "Meus orçamentos" e não vê "Painel"; Admin vê "Painel"; POST logout funciona a partir do site. Use `assertInertia` nos props compartilhados, não em HTML.

## Encerramento

`vendor/bin/pint --format agent app tests` + `vendor/bin/phpstan analyse` + `php artisan test --compact` + `npm run build && npm run lint`. Registrar regra durável via `record-rule` se decidir algo não-óbvio (ex.: shape do `auth.user` compartilhado).

## Registro de execução

**Executado em 2026-08-24.** Todos os passos concluídos; `pint`, `phpstan`, `php artisan test --compact` (203 passed), `npm run build`, `npm run lint` e `npm run types:check` verdes.

- `HandleInertiaRequests::share()` agora expõe `auth.user` como array explícito (`id`, `name`, `email`, `role`, `email_verified_at`) ou `null` para guest. `email_verified_at` foi incluído além do plano porque `settings/Profile.vue` depende dele (banner de verificação de e-mail).
- Tipo TS `Auth.user` virou `User | null` (id é ULID string, role tipado); componentes de contexto autenticado (`NavUser`, `AppHeader`, `settings/Profile`) usam `auth.user!`.
- Criado `site/SiteUserMenu.vue` com variantes desktop (dropdown Reka/shadcn retematizado com `site-*`) e mobile (`mobile` prop, links achatados no menu hambúrguer). Customer vê "Meus orçamentos"; Admin/Manager veem "Painel"; "Sair" via `<Link :href="logout()" as="button">` + `router.flushAll()`.
- Rodapé: link "Área do cliente" aponta sempre para `login()` — autenticado é redirecionado pelo `redirectUsersTo`/`RoleRedirect`, evitando duplicar lógica de papel no front (regra `.ai/rules/support.md`).
- **Desvio**: não foi criado o espelho `lang/en/app/site.php` — o diretório `lang/en/app/` não existe para nenhum grupo; seguiu-se a convenção atual (apenas `lang/pt_BR/app/*`). Chaves novas em `app.site.auth.*`.
- Testes em `tests/Feature/Site/SiteAuthMenuTest.php` (6 testes, via `assertInertia` nos props compartilhados): guest com `auth.user` null, shape enxuto p/ customer, campos sensíveis ausentes, roles admin/manager, e POST logout a partir do site.
- Regra durável registrada em `.ai/rules/components-site.md` (shape do `auth.user`).
