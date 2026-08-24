# 00 — Contexto Compartilhado

> Documento de referência. Não é um plano executável — os planos 01–07 remetem a ele.

## O negócio

**Estátuas Oliveira** — empresa com mais de 25 anos fabricando estátuas, vasos, fontes, bancos e decorações em cimento para lojas de jardinagem. Site institucional + catálogo de produtos (sem checkout — o contato/orçamento é por telefone/WhatsApp/formulário). Conteúdo em pt-BR.

## Projetos envolvidos

| | Caminho | Stack |
|---|---|---|
| **Legado** | `/home/caltj/projects/estatuasoliveira` | Laravel 6, PHP 7.2, Blade, webpack |
| **Novo (este)** | `/home/caltj/projects/estatuasoliveirav1` | Laravel 13, PHP 8.5, Inertia v3 + Vue 3 + TS, Tailwind 4, Fortify, Wayfinder, Pest 5 |
| **Dump legado** | `database/estatuas_db.sql` (neste projeto) | MySQL, PKs em UUID `char(36)` |
| **Novo design** | `stitch_est_tuas_oliveira_ui_redesign/` (neste projeto) | HTML/Tailwind estático + screenshots |

O projeto novo já tem: auth via Fortify (login, 2FA, passkeys), páginas `Dashboard.vue`, `Welcome.vue`, `auth/`, `settings/`, `teams/`, models `User/Team/Membership/TeamInvitation`. O conceito de **Teams não será usado pelo domínio** — a empresa é única; manter o scaffolding de auth, ignorar/ocultar teams na UI se atrapalhar.

## Banco legado (dump `database/estatuas_db.sql`)

Tabelas: `address`, `categories`, `companies`, `files`, `items`, `orders`, `password_resets`, `permissions`, `permission_role`, `permission_user`, `products`, `providers`, `roles`, `role_user`, `sliders`, `users`, `migrations`.

Características (e problemas) do schema legado:
- PKs UUID `char(36)`; **toda** tabela carrega `company_id` + `user_id` (multi-tenant desnecessário — só existe 1 empresa real).
- `status` é enum string `('deleted','draft','published')` redundante com `deleted_at`.
- `files` é polimórfica (`fileable_type`/`fileable_id`) com colunas camelCase (`fullPath`, `fileType`) e caminhos físicos `/dist/upload/images/...`.
- `products`: `width` e `stoke` (sic, = stock) são **varchar**; `featured` é enum `('yes','no')`; sem preço.
- `orders`/`items`: orçamentos simples (qty, price, total decimal 18,4).
- `providers`, `address`, `companies` (17 registros, mas só 1 empresa real: slug de "Estátuas Oliveira"), roles/permissions caseiros.

## Banco novo (decidido no plano 01 — resumo)

ULID em todas as PKs, empresa única (dados institucionais em `settings`), soft deletes nativos, sem `company_id`/`user_id` espalhados. Tabelas de domínio: `categories`, `products`, `product_images` (via `media`), `sliders`, `quotes` + `quote_items` (ex-orders/items), `contact_messages`, `settings`. Roles simples via coluna `role` no `users` (admin/editor) — sem pacote de permissão enquanto não houver necessidade.

## Design systems (dois temas selecionáveis)

Pasta `stitch_est_tuas_oliveira_ui_redesign/`:

1. **Serene Stone & Silver** (`serene_stone_silver/DESIGN.md`) — claro, tons de pedra/prata, primária navy (#0F2C59-like), fonte display EB Garamond + Manrope. As telas `*_navy_version` usam este tema.
2. **Terracotta Earth / Zen Earth & Stone** (`terracotta_earth/DESIGN.md`) — tons terrosos (terracota #B13...(ver DESIGN.md), bege #F4F1EA, marrom), EB Garamond + Manrope. As telas `*_1`/`*_2` usam este tema.

**Requisito**: o visitante e o admin podem **escolher o tema** (toggle no site + padrão configurável no admin). Implementação: design tokens em CSS custom properties, atributo `data-theme="stone" | "terracotta"` no `<html>`, persistência em cookie/localStorage + setting global. Detalhes no plano 03.

Telas de referência (cada pasta tem `code.html` + `screen.png`):
- Home: `home_est_tuas_oliveira_1|2`, `home_est_tuas_oliveira_navy_version` + versões `home_mobile_*`
- Nossa História: `nossa_hist_ria_1|2`, `nossa_hist_ria_navy_version`
- Galeria (Budas/produtos): `budas_galeria_1|2`, `budas_galeria_navy_version`
- Contato: `contato_est_tuas_oliveira`, `contato_mobile_*`, `contato_navy_version`
- Termos e Política: `termos_e_pol_tica_est_tuas_oliveira`, `termos_e_pol_tica_mobile_*`
- Conteúdo textual real do site atual: `extracted_text_from_https_estatuasoliveira.com.br.md`

## Convenções de código

- Models com `HasUlids`, `SoftDeletes` quando aplicável, casts tipados, factories completas.
- Controllers finos; validação em Form Requests; nomes descritivos.
- Frontend: páginas em `resources/js/pages/` (públicas em `site/`, admin em `admin/`), componentes reutilizáveis em `resources/js/components/`; rotas sempre via Wayfinder (`@/routes`, `@/actions`).
- Ativar as skills relevantes por domínio (`laravel-best-practices`, `pest-testing`, `inertia-vue-development`, `tailwindcss-development`, `wayfinder-development`, `fortify-development`) antes de codar.
- Registrar decisões duráveis com `record-rule` (Boost) para os próximos chats.
