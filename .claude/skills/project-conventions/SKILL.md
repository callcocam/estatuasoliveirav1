---
name: project-conventions
description: >-
  Convenções deste projeto (Estátuas Oliveira) consolidadas das sessões
  "Traduções de arquitetura" e "Migração do layout para pages". ATIVE sempre
  que: criar ou editar pages Vue/Inertia (layout, título, breadcrumbs), exibir
  qualquer texto na UI ou em e-mails (traduções useT/__()), criar migrations,
  models ou tabelas novas (PKs ULID), ou encontrar tabelas/colunas com
  autoincrement. Cobre: defineOptions({ layout }), setLayoutProps, useT(),
  lang/pt_BR/app/*.php, MergingFileLoader, HasUlids, foreignUlid.
---

# Convenções do projeto

Três convenções obrigatórias: **layout definido na page** (nunca global), **traduções via árvore `app.*`** (nunca texto hardcoded) e **PKs ULID** (nunca autoincrement).

## 1. Layout nas pages (não em app.ts)

`resources/js/app.ts` NÃO resolve layout globalmente — não reintroduza callback `layout` lá. Cada page declara o próprio layout (persistente: não re-monta entre navegações):

```ts
import AuthLayout from '@/layouts/AuthLayout.vue';

defineOptions({ layout: AuthLayout });

setLayoutProps({
    title: 'app.auth.login.title',        // chave de tradução, não texto
    description: 'app.auth.login.description',
});
```

Padrões por área:

| Pages | Layout | layoutProps |
| --- | --- | --- |
| `pages/auth/**` | `AuthLayout` | `{ title, description }` — **chaves** `app.*`; o layout traduz com `t()` |
| `pages/settings/**` | `[AppLayout, SettingsLayout]` (aninhado) | `{ breadcrumbs: [{ title: 'app...', href: rota() }] }` |
| Dashboard/app | `AppLayout` | `{ breadcrumbs }` |
| `pages/admin/**` | `AdminLayout` | ver `.ai/rules/admin.md` |
| `pages/site/**` | `SiteLayout` | ver `.ai/rules/site.md` |
| `Welcome` | sem layout | — |

Regras:
- `setLayoutProps` é importado de `@inertiajs/vue3` e chamado no `<script setup>`, fora de qualquer hook. Para valores reativos (ex.: breadcrumb com prop dinâmica), use `watchEffect` ou passe computed conforme já feito em pages existentes — copie um sibling.
- Breadcrumbs que dependem de props compartilhadas usam `usePage().props.*`.
- `href` de breadcrumb sempre via Wayfinder (`@/routes/...` ou `@/actions/...`), nunca URL hardcoded.

## 2. Traduções (arquitetura `app.*`)

Documento base: `docs/traducoes-arquitetura.md`. Regra registrada em `.ai/rules/js.md`.

- **Backend**: locale `pt_BR`, fallback `en`. Arquivos por grupo em `lang/pt_BR/app/*.php` (`auth.php`, `settings.php`, `nav.php`, `common.php`, `mail.php`, `site.php`, `admin.php`, ...), mesclados pelo `MergingFileLoader` (`App\Support\Translation`) sob a chave `app.*` e compartilhados via `HandleInertiaRequests::share()` como prop `translations`. E-mails e mensagens de backend usam `__('app.grupo.chave')`.
- **Frontend**: composable `useT()` (`resources/js/composables/useT.ts`):

```ts
import { useT } from '@/composables/useT';
const { t } = useT();
// template: {{ t('app.settings.profile.title') }}
// placeholders Laravel: t('app.x.y', { name: user.name }) → substitui :name
```

- **Nunca** hardcode texto visível em componentes Vue nem em código PHP de resposta ao usuário. Chave nova → adicionar no arquivo do grupo em `lang/pt_BR/app/` (e no equivalente `en` se existir).
- Chave desconhecida retorna a própria chave na tela — se aparecer `app.foo.bar` renderizado, falta registrar a tradução.
- **Testes**: asserir mensagens via `__('app...')`/`__('validation...')`, nunca strings em inglês/português fixas.

## 3. ULID em vez de autoincrement

Regra registrada em `.ai/rules/migrations.md`: **todas as PKs de tabelas próprias são ULID**.

Para **tabelas novas** (sem perguntar — é o padrão):

```php
// migration
$table->ulid('id')->primary();
$table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
```

```php
// model
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Product extends Model
{
    use HasFactory, HasUlids, SoftDeletes;
}
```

- Type hints de ids são `?string`/`string`, **nunca** `int` (métodos, form requests, `ProfileValidationRules`, rotas, etc.).
- Soft deletes em todas as tabelas de domínio (`deleted_at` + `SoftDeletes`) — ver `.ai/rules/models.md`.
- Status como `string(20)` com cast para enum (`PublishStatus`/`QuoteStatus`/`UserRole`).

Para **tabelas existentes** com `$table->id()`/autoincrement encontradas durante o trabalho:

1. **Pare e pergunte ao usuário** (via AskUserQuestion) se deve converter para ULID agora — não converta por conta própria, pois a conversão altera FKs, dados existentes e assinaturas.
2. Se autorizado, a conversão inclui: migration alterando PK e todas as FKs para `ulid`/`foreignUlid` (com migração de dados quando a tabela tem registros), `HasUlids` no model, atualização de type hints `int` → `string` em todo o código que referencia o id, e testes rodando verdes.
3. Se negado, mantenha o autoincrement e siga — mas não crie FKs novas apontando para ela sem alinhar o tipo.

## 4. Verificação (sempre, nesta ordem)

Frontend alterado:
- `npx vue-tsc --noEmit`
- `npx eslint` / `npx prettier --check` nos arquivos tocados (ou scripts do package.json)
- `npm run build`

PHP alterado:
- `vendor/bin/pint --dirty --format agent`
- `php artisan test --compact --filter=<área afetada>` (testes de tradução usam `__()`)

## Armadilhas conhecidas (das sessões originais)

- Não há testes de browser (só Feature/Unit, que não renderizam Vue) — typecheck + build são a verificação de frontend.
- Trabalho paralelo pode tocar as mesmas pages: integre mudanças de outras sessões (ex.: chaves i18n já migradas), nunca reverta.
- O recurso de **teams foi removido** (models, tabelas, rotas, pages) em 2026-08-24 — não recrie nem referencie.
- Antes de editar qualquer arquivo, leia `.ai/rules/index.md` e os rule files cujos globs cobrem o caminho — esta skill complementa, não substitui, as regras de `.ai/rules`.
