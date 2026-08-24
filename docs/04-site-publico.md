# 04 — Site Público

> Pré-requisitos: planos 01 (banco) e 03 (design system). Leia `docs/00-contexto.md`. Pode rodar em paralelo com o plano 05.

## Objetivo

Construir as páginas públicas com Inertia v3 + Vue, fiéis ao redesign (nos dois temas), consumindo o banco novo.

## Referências obrigatórias

Antes de codar, abrir o `code.html` e o `screen.png` de cada tela em `stitch_est_tuas_oliveira_ui_redesign/` (desktop + mobile + navy_version) e o conteúdo real em `extracted_text_from_https_estatuasoliveira.com.br.md`. O HTML das telas é referência de layout — **reimplementar com os tokens/componentes do plano 03**, não copiar classes com cores fixas.

## Páginas e rotas (nomes de rota entre parênteses)

| Rota | Página Vue (`resources/js/pages/site/`) | Conteúdo |
|---|---|---|
| `/` (`home`) | `Home.vue` | Hero com sliders (tabela `sliders` + media), destaque institucional (25+ anos), produtos em destaque (`featured`+`published`), categorias, CTA orçamento/WhatsApp |
| `/nossa-historia` (`about`) | `About.vue` | História da empresa (conteúdo de settings/extracted text), imagens |
| `/produtos` (`products.index`) | `products/Index.vue` | Catálogo com filtro por categoria (querystring), busca por nome, paginação (usar infinite scroll do Inertia v3 no mobile, paginação clássica no desktop se o design pedir) |
| `/produtos/{product:slug}` (`products.show`) | `products/Show.vue` | Detalhe: galeria de imagens, dimensões, referência, produtos relacionados (mesma categoria), CTA orçamento |
| `/galeria` (`gallery`) | `Gallery.vue` | Grade de imagens estilo `budas_galeria_*` (media de produtos publicados, lightbox) |
| `/contato` (`contact`) | `Contact.vue` | Formulário (name, email, phone, subject, message) → grava `contact_messages` + dados da empresa (settings): telefone, WhatsApp, endereço, mapa |
| `/termos-e-politica` (`terms`) | `Terms.vue` | Conteúdo estático de settings (editável no admin, plano 05) |

Layout compartilhado `resources/js/layouts/SiteLayout.vue`: header (nav + logo + `ThemeSwitcher`), footer (dados de settings), menu mobile, SEO por página.

## Backend

- Controllers em `app/Http/Controllers/Site/`: `HomeController`, `AboutController`, `ProductController` (index/show), `GalleryController`, `ContactController` (show/store), `TermsController`. Somente registros `published` e não deletados (scopes `published()` nos models).
- `ContactStoreRequest` com validação + honeypot simples; e-mail de notificação via `Mail` (mailable `ContactMessageReceived` para `Setting::get('company.email')`) em fila `queue`d — se mail não estiver configurado, `log` mailer.
- Props Inertia otimizadas: `Inertia::defer()` para produtos relacionados e galeria pesada, com skeleton animado (regra do CLAUDE.md); imagens com `srcset` a partir de `media` (dimensões salvas).
- SEO: title/description por página, Open Graph, sitemap (`/sitemap.xml` via controller simples), rota canonical por slug.
- Wayfinder: todos os links/forms via `@/routes` / `@/actions` (ativar skill `wayfinder-development`); rodar `php artisan wayfinder:generate` quando criar rotas.

## Passos

1. Ativar skills: `inertia-vue-development`, `tailwindcss-development`, `wayfinder-development`, `laravel-best-practices`, `pest-testing`. Ler regras em `.ai/rules` deixadas pelos planos 01/03.
2. Rotas nomeadas em `routes/web.php` (agrupadas), controllers, requests.
3. `SiteLayout.vue` + páginas na ordem: Home → Products → Show → Gallery → About → Contact → Terms. Mobile-first, breakpoints conforme telas `*_mobile_*`.
4. Estados vazios elegantes (sem produtos, busca sem resultado) e página 404 pública no tema.
5. **Testes Pest** (feature por controller): status 200, componente Inertia correto, props (só published aparece, draft/deleted não), filtro por categoria, envio do formulário de contato (grava + notifica + valida), sitemap. Browser test de fumaça nas 7 rotas nos **dois temas** (sem erros de JS).
6. `vendor/bin/pint --dirty --format agent`; `npm run build`; `record-rule` para convenções novas de `resources/js/pages/site/**`.

## Critérios de aceite

- [x] 7 páginas navegáveis, fiéis ao design nos dois temas, responsivas
- [x] Nenhuma cor hardcoded (só tokens)
- [x] Formulário de contato persiste e notifica
- [x] Testes feature passando (browser smoke pendente — ver nota)
- [x] `npm run build` limpo

## Registro de execução

Executado em 2026-08-23.

- **Rotas** (`routes/web.php`): `home` (/), `about` (/nossa-historia), `products.index|show` (/produtos), `gallery` (/galeria), `contact`+`contact.store` (/contato), `terms` (/termos-e-politica), `sitemap` (/sitemap.xml). Wayfinder regenerado com `--with-form` (atenção: sem essa flag os `.form()` usados pelo app somem).
- **Backend**: controllers em `app/Http/Controllers/Site/`; `ContactStoreRequest` com honeypot `website` (descarte silencioso); mailable em fila `ContactMessageReceived` para `Setting::get('contact_email')`; `App\Support\CompanyProfile` compartilhado como prop `site` no `HandleInertiaRequests`; `App\Support\ProductPresenter::card()` para shape de cards; 404 pública via `$exceptions->respond()` em `bootstrap/app.php` renderizando `site/Error`.
- **Frontend**: `layouts/SiteLayout.vue` (Head/SEO + header + footer), componentes `site/SiteHeader|SiteFooter|ProductCard|SectionHeading`, páginas em `pages/site/` (Home com hero slider, Products com `Inertia::scroll` + `<InfiniteScroll>`, Show com galeria + relacionados via `Inertia::defer`, Gallery com lightbox, About, Contact com `<Form>`, Terms, Error). Traduções em `lang/pt_BR/app/site.php` (`t('app.site.*')`). `Welcome.vue` removido (rota `/` agora é a Home do site).
- **Testes**: `tests/Feature/Site/` — 17 testes (published-only, filtros, busca, deferred via `loadDeferredProps`, contato grava+notifica+valida+honeypot, sitemap, 404). Suíte completa: 145 passed. Corrigida tipagem pré-existente `Team/TeamMember.id` (number → string, ULIDs).
- **Pendência**: browser smoke tests nos dois temas exigem `pestphp/pest-plugin-browser` (dependência nova — precisa de aprovação; não instalada).
- Regra registrada em `.ai/rules/site.md`.
