# 01 — Banco de Dados (ULID + estrutura nova)

> Pré-requisitos: nenhum. Leia `docs/00-contexto.md` antes. Pode rodar em paralelo com o plano 03.

## Objetivo

Criar o schema novo do zero com **ULID em todas as PKs**, simplificando o modelo legado (remoção do multi-tenant `company_id`/`user_id`, remoção do enum `status='deleted'`, tipos corretos). Entregar migrations, models, factories, seeders e testes.

## Decisões de modelagem (já tomadas — não rediscutir)

1. **Empresa única**: sem tabela `companies`/`providers`/`address` do legado. Dados institucionais (nome, CNPJ, telefones, WhatsApp, e-mail, endereço, redes sociais, tema padrão) vivem em `settings` (key/value tipado).
2. **`status`**: enum PHP `App\Enums\PublishStatus: Draft|Published` (string no banco). Exclusão é só `SoftDeletes` — o valor legado `deleted` vira `deleted_at` preenchido na migração de dados.
3. **Roles**: coluna `role` no `users` — enum PHP `App\Enums\UserRole: Admin|Editor`. Sem tabelas de permission por ora.
4. **Mídia**: tabela única polimórfica `media` (substitui `files`), com `disk`, `path`, `collection` (ex.: `cover`, `gallery`), `sort_order`, dimensões e metadados em json.
5. **Orçamentos**: `quotes` + `quote_items` (ex-`orders`/`items`), com snapshot de nome/preço do produto no item.
6. **Contato**: nova tabela `contact_messages` (formulário do site — o legado não persistia).

## Schema alvo

Todas as tabelas: `ulid('id')->primary()`, `timestamps()`; soft deletes onde indicado. FKs são `foreignUlid()->constrained()`.

```
users            id, name, email(unique), phone?, password, role(default editor),
                 status, remember_token, timestamps, softDeletes
                 (+ colunas que o Fortify/scaffolding atual já criou — preservar)

categories       id, name, slug(unique), description?, status, sort_order(int,0),
                 timestamps, softDeletes

products         id, category_id FK, name, slug(unique), reference?(string50),
                 description?(text), price?(decimal 12,2, nullable — site não expõe preço),
                 width_cm?(unsignedSmallInt), height_cm?(unsignedSmallInt),
                 weight_kg?(decimal 8,2), stock(unsignedInt,0), featured(bool,false),
                 status, timestamps, softDeletes

media            id, mediable_type+mediable_id (morph, index), collection(string, 'default'),
                 disk('public'), path, file_name, mime_type?, size?(unsignedBigInt),
                 width?, height?, alt?, sort_order(int,0), custom_properties(json?), timestamps

sliders          id, title, subtitle?, cta_label?, cta_url?, sort_order(int,0),
                 status, timestamps, softDeletes

quotes           id, user_id? FK(nullOnDelete), customer_name, customer_email?,
                 customer_phone?, notes?(text),
                 status: enum QuoteStatus Pending|Answered|Closed (default pending),
                 total(decimal 12,2, 0), timestamps, softDeletes

quote_items      id, quote_id FK(cascade), product_id? FK(nullOnDelete),
                 product_name (snapshot), qty(unsignedInt), unit_price(decimal 12,2),
                 total(decimal 12,2), timestamps

contact_messages id, name, email, phone?, subject?, message(text),
                 read_at?(timestamp), timestamps

settings         id, key(unique), value(json?), timestamps
```

Índices além dos unique: `products(status, featured)`, `products(category_id, status)`, `media(mediable_type, mediable_id, collection)`, `sliders(status, sort_order)`.

## Passos

1. **Skills e regras**: ativar `laravel-best-practices` e `pest-testing`. Conferir migrations existentes (`database/migrations`) do scaffolding (users/teams) — **não apagar** o que Fortify usa; criar migration para alterar `users` (adicionar `phone`, `role`, `status`, softDeletes) respeitando o que já existe. Verificar antes com `database-schema` (Boost).
2. **Enums**: `app/Enums/PublishStatus.php`, `UserRole.php`, `QuoteStatus.php` (backed string, TitleCase keys).
3. **Migrations**: `php artisan make:migration ... --no-interaction` na ordem de dependência (categories → products → media → sliders → quotes → quote_items → contact_messages → settings → alter users).
4. **Models** (`php artisan make:model -f`): `Category`, `Product`, `Media`, `Slider`, `Quote`, `QuoteItem`, `ContactMessage`, `Setting`. Todos com `HasUlids`; casts para enums/decimais/json; relacionamentos:
   - `Category hasMany Product`; `Product belongsTo Category`, `morphMany Media` (+ helpers `cover()`, `gallery()`); `Slider morphMany Media`; `Quote hasMany QuoteItem`; `QuoteItem belongsTo Product`.
   - `Setting`: helpers estáticos `Setting::get(key, default)` / `Setting::set(key, value)` com cache (`Cache::rememberForever` + flush no set).
   - Slugs: gerar automaticamente no creating quando ausente (observer ou boot no model).
5. **Factories** completas + states úteis (`published()`, `draft()`, `featured()`).
6. **Seeders**: `DatabaseSeeder` chama `SettingsSeeder` (dados institucionais reais — extrair de `stitch_est_tuas_oliveira_ui_redesign/extracted_text_from_https_estatuasoliveira.com.br.md`), `AdminUserSeeder` (admin com e-mail `contato@estatuasoliveira.com.br`, senha via env `ADMIN_INITIAL_PASSWORD` ou aleatória exibida no output) e, apenas em local, `DemoSeeder` com dados fake.
7. **Testes Pest** (feature): criação via factory de cada model verificando ULID válido (26 chars), casts, relacionamentos, soft delete, `Setting::get/set` com cache, geração de slug. Rodar `php artisan test --compact`.
8. **Finalizar**: `vendor/bin/pint --dirty --format agent`; registrar via `record-rule` (glob `database/migrations/**` e `app/Models/**`): "PKs sempre ULID (`HasUlids`/`foreignUlid`); status via enum `PublishStatus`; exclusão só por SoftDeletes".

## Critérios de aceite

- [x] `php artisan migrate:fresh --seed` roda limpo
- [x] Todas as PKs são ULID; nenhuma tabela tem `company_id`
- [x] Enums PHP em uso nos casts
- [x] Testes passando
- [x] Regras registradas em `.ai/rules`

## Registro de execução

_(preencher ao executar)_

Executado em 2026-08-21:

- ULID aplicado também às tabelas do scaffolding (users, teams, team_members, team_invitations, passkeys.user_id, sessions.user_id) editando as migrations base — projeto ainda não deployado. Exceção deliberada: PK de `passkeys`, `cache` e `jobs` permanece do framework/vendor.
- Criados: enums `PublishStatus`, `UserRole`, `QuoteStatus`; migrations 2026_08_21_000001..000008; models Category, Product, Media, Slider, Quote, QuoteItem, ContactMessage, Setting (+ concern `HasMedia`); factories e seeders (SettingsSeeder com dados reais, AdminUserSeeder idempotente com senha via `ADMIN_INITIAL_PASSWORD` ou gerada, DemoSeeder apenas em `local`).
- Ajustes de tipo por causa do ULID: `GeneratesUniqueTeamSlugs::generateUniqueTeamSlug`, `ProfileValidationRules` e DTO `UserTeam` agora recebem `?string`; teste de exclusão de conta atualizado para soft delete.
- Verificação: `migrate:fresh --seed` limpo no container; suíte completa 111 passed (374 assertions); Pint ok; regras registradas em `.ai/rules/migrations.md` e `.ai/rules/general.md`.
