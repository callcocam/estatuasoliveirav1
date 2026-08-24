# 05 — Painel Administrativo

> Pré-requisitos: planos 01 (banco) e 03 (design system). Leia `docs/00-contexto.md`. Pode rodar em paralelo com o plano 04.

## Objetivo

Área administrativa completa em `/admin`, protegida por auth (Fortify já instalado) + gate de role, para gerenciar todo o conteúdo do site.

## Acesso e autorização

- Middleware group `auth` + `EnsureUserIsAdmin` (role `Admin` acessa tudo; `Editor` acessa conteúdo mas não Usuários/Configurações). Policies por model (`ProductPolicy` etc.) — simples, baseadas no enum `UserRole`.
- Aproveitar o scaffolding existente (`Dashboard.vue`, `settings/` de perfil, 2FA, passkeys). O dashboard atual vira o dashboard do admin (mover/adaptar para `resources/js/pages/admin/Dashboard.vue`). Ocultar UI de Teams se presente na navegação.

## Módulos (CRUDs)

Rotas resource em `routes/web.php` sob prefixo/nome `admin.`, controllers em `app/Http/Controllers/Admin/`, páginas em `resources/js/pages/admin/`:

| Módulo | Recursos |
|---|---|
| **Dashboard** | Cards: totais (produtos publicados/rascunho, categorias, orçamentos pendentes, mensagens não lidas), últimas mensagens e orçamentos |
| **Categorias** | CRUD + ordenação (`sort_order` drag-and-drop), contagem de produtos, soft delete/restore |
| **Produtos** | CRUD completo: dados, dimensões, estoque, destaque, status draft/published, categoria; **upload de imagens** (cover + galeria) com ordenação e alt; duplicar produto; filtros por status/categoria/busca na listagem |
| **Sliders** | CRUD + imagem + ordenação + status |
| **Orçamentos (quotes)** | Listagem com filtro por status, detalhe com itens, mudança de status (pending/answered/closed), criação manual de orçamento com busca de produtos |
| **Mensagens de contato** | Listagem, marcar lida/não lida, detalhe, excluir |
| **Usuários** (Admin only) | CRUD, definição de role, reset de senha por link |
| **Configurações do site** (Admin only) | Form de settings agrupado: dados da empresa (nome, CNPJ, telefones, WhatsApp, e-mail, endereço, redes), textos institucionais (história, termos/política — textarea/markdown), **tema padrão do site** (`stone`/`terracotta` com preview), logo |

## Upload de mídia

- Endpoint dedicado `admin.media.store`/`destroy`/`reorder` (controller `MediaController`) — upload para disk `public` via `Storage`, gerando `path` por model (`products/{ulid}/...`), salvando dimensões (getimagesize) e registro `media`. Validar mime/size. Componente Vue `MediaUploader.vue` (drag-and-drop, preview, progresso via `useHttp` do Inertia v3, ordenação).
- Conversões/thumbnails: gerar thumb simples com GD se disponível; caso contrário registrar como melhoria no plano 07 (não adicionar dependência sem aprovação).

## UI do admin

- Layout `resources/js/layouts/AdminLayout.vue`: sidebar colapsável, topbar com busca global (produtos), user menu, breadcrumbs. Usa os mesmos tokens de tema do plano 03 (admin também respeita o `ThemeSwitcher`).
- Tabelas com paginação server-side, ordenação e filtros na querystring; forms com `<Form>`/`useForm` do Inertia v3 e validação server-side exibida por campo; toasts com `vue-sonner` (já instalado); dialogs de confirmação para destroy.
- Form Requests para todo store/update; slugs regenerados com unicidade.

## Passos

1. Ativar skills: `fortify-development`, `inertia-vue-development`, `wayfinder-development`, `laravel-best-practices`, `pest-testing`, `tailwindcss-development`. Ler `.ai/rules`.
2. Middleware/policies + estrutura de rotas `admin.`.
3. `AdminLayout.vue` + Dashboard.
4. CRUDs na ordem: Categorias → Produtos (com MediaUploader) → Sliders → Orçamentos → Mensagens → Usuários → Configurações.
5. **Testes Pest** por módulo: autorização (guest redirect, editor bloqueado onde deve), CRUD feliz + validação, upload de mídia (Storage::fake), reorder, mudança de status de quote, settings salvando tema padrão. Browser smoke do fluxo principal (login → criar produto com imagem → publicar → aparece no site).
6. `vendor/bin/pint --dirty --format agent`; `npm run build`; `record-rule` para convenções de `app/Http/Controllers/Admin/**` e `resources/js/pages/admin/**`.

## Critérios de aceite

- [ ] Todos os módulos funcionais com autorização correta
- [ ] Upload/ordenação de imagens funcionando
- [ ] Tema padrão do site configurável pelo admin
- [ ] Testes passando; `npm run build` limpo

## Registro de execução

**Concluído em 2026-08-23.**

- **Autorização**: `EnsureUserIsAdmin` (Admin+Manager; parâmetro `:admin` restringe a Admin). O enum existente é `UserRole::Manager` — usado como o papel "Editor" citado no plano (acessa conteúdo; bloqueado em Usuários/Configurações). Policies por model foram dispensadas: as regras são só de role e o middleware cobre tudo (testado).
- **Rotas**: `routes/admin.php` (required no `web.php`), prefixo/nome `admin.`, wayfinder regenerado. Bindings: Category/Product por slug; demais por ULID.
- **Dashboard**: novo em `/admin` (`admin/Dashboard.vue`) com cards de totais + últimas mensagens/orçamentos. O dashboard de teams existente foi **mantido** (não movido) para não quebrar fluxo de auth/teams e seus testes; admins acessam `/admin` diretamente.
- **Módulos**: Categorias (modal + reorder + restore), Produtos (Index com filtros status/categoria/busca/excluídos, Form com MediaUploader, duplicar copia arquivos de mídia, restore), Sliders (Index + reorder, Form com imagem), Orçamentos (filtro, detalhe com itens, mudança de status, criação manual com busca de produtos via partial reload `productResults`), Mensagens (filtro lida/não lida, show marca como lida, toggle, excluir), Usuários (modal CRUD, reset de senha via `Password::sendResetLink`, autoexclusão bloqueada), Configurações (empresa/contato/endereço/termos/tema padrão `site_default_theme` com preview + upload de logo `branding_logo_path`).
- **Mídia**: `MediaController` (store/update-alt/reorder/destroy) com allowlist `product|slider|category`, disk `public`, path `{plural}/{ulid}/…`, dimensões via getimagesize em `custom_properties`. Thumbnails/conversões **não** implementadas — registrar como melhoria no plano 07.
- **UI**: `AdminLayout.vue` + `AdminSidebar` (grupos Catálogo/Atendimento/Administração, itens de Admin ocultos para Manager, link "Ver site"); componentes `MediaUploader`, `AdminPagination`, `ConfirmDeleteDialog`; reordenação por setas (não drag-and-drop) — upload aceita drag-and-drop de arquivos.
- **i18n**: `lang/pt_BR/app/admin.php` (`app.admin.*`), toasts via `Inertia::flash('toast', …)`.
- **Testes**: 48 testes Pest em `tests/Feature/Admin/` (autorização, CRUDs felizes + validação, upload/reorder/destroy de mídia com `Storage::fake`, status de quote, settings com tema e logo). Suíte completa: 195 passed. Browser smoke não executado (sem runner de browser no stack) — coberto por feature tests do fluxo criar→publicar (produto publicado aparece no site já coberto pelos testes do plano 04).
- `pint` ok, `eslint`/`prettier` ok, `npm run build` e `vue-tsc` limpos. Regra registrada em `.ai/rules/admin.md`.
- **Pendência/nota**: redirecionamento pós-login continua indo ao dashboard de teams; admins navegam para `/admin` (link pode ser adicionado ao menu do usuário numa iteração futura, se desejado).
