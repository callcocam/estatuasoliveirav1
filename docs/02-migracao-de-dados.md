# 02 — Migração de Dados (dump legado → schema novo)

> Pré-requisito: plano 01 concluído. Leia `docs/00-contexto.md`.

## Objetivo

Importar os dados reais de `database/estatuas_db.sql` (MySQL, PKs UUID) para o schema novo (ULID), com mapeamento e limpeza. A migração física dos arquivos de imagem é o plano 06 — aqui só se migram os **registros**.

## Estratégia

Criar um comando Artisan idempotente: `php artisan legacy:import {--fresh}`.

- **Fonte**: carregar o dump numa conexão MySQL secundária `legacy` (config `database.connections.legacy`), OU — preferível, para não exigir MySQL — **parsear o dump** com um leitor de `INSERT INTO` dedicado (classe `App\Support\LegacySqlReader` que extrai linhas por tabela via regex/parser tolerante). Decidir no início do chat conforme ambiente disponível; documentar a escolha no registro de execução.
- **Mapa UUID→ULID**: gerar um ULID novo para cada UUID encontrado e manter dicionário em memória (e persistir em `storage/app/legacy-id-map.json` para o plano 06 reutilizar nos caminhos de arquivos).
- **Idempotência**: `--fresh` trunca as tabelas de domínio antes; sem flag, pula registros já importados (procurar por slug/chave natural).

## Mapeamentos

| Legado | Novo | Observações |
|---|---|---|
| `categories` | `categories` | `status='deleted'` → soft delete; demais `draft/published` → enum |
| `products` | `products` | `stoke`(varchar) → `stock` int (fallback 0); `width`(varchar) → `width_cm` int quando numérico, senão null e anotar no relatório; `featured` `yes/no` → bool; `category_id` via mapa |
| `files` | `media` | somente `fileable_type` de Product/Slider/Company; `fullPath`/`dir` → `path` normalizado (remover prefixo `/dist/upload/images`); `assets` → `collection` (mapear: capa → `cover`, demais → `gallery`); manter `width/height/size`; morph type novo (`App\Models\Product` etc.). Files de companies → media de settings? Não: logo da empresa vira setting `branding.logo_path` (registrar caminho). |
| `sliders` | `sliders` | `name` → `title`; `description` → `subtitle` |
| `orders` + `items` | `quotes` + `quote_items` | `status='featured'` legado → tratar como `answered`; snapshot `product_name` a partir do produto mapeado; recalcular `total` e comparar com o legado (divergência → relatório) |
| `users` | `users` | apenas usuários não deletados; `role`: quem tem role legada `admin`/special `all-access` → `Admin`, senão `Editor`; senhas bcrypt legadas são compatíveis — **preservar hash**; e-mails duplicados/vazios → relatório e skip |
| `companies` | `settings` | somente a empresa real (slug `estatuas-oliveira`/e-mail `contato@estatuasoliveira.com.br`): phone, document (CNPJ), description → settings `company.*`. Demais registros: ignorar |
| `address` | `settings` | endereço da empresa real → `company.address.*`; endereços de users: ignorar |
| `providers`, `roles`, `permissions`, pivots, `password_resets`, `migrations` | — | não migrar |

Timestamps: preservar `created_at`/`updated_at`/`deleted_at` originais.

## Passos

1. Ler o dump para confirmar volumetria e valores distintos de `status`, `assets` (files) e `special` (roles) antes de codar o mapeamento.
2. Implementar `LegacySqlReader` (ou conexão `legacy`) + `App\Console\Commands\LegacyImportCommand` com uma classe importer por tabela (`app/Services/LegacyImport/*Importer.php`), rodando em transação por tabela.
3. Relatório final no console + `storage/app/legacy-import-report.json`: contagens origem→destino, registros pulados e por quê.
4. Persistir `storage/app/legacy-id-map.json` (uuid → ulid, por tabela) — o plano 06 depende disso.
5. **Testes Pest**: fixture SQL reduzida em `tests/Fixtures/legacy-sample.sql` (montar com 2–3 linhas reais de cada tabela extraídas do dump) cobrindo: conversão status/featured/stock, morph de media, mapeamento de FKs, idempotência (rodar 2× não duplica), preservação de hash de senha.
6. Rodar de verdade: `php artisan legacy:import --fresh` e conferir contagens com o relatório.
7. `vendor/bin/pint --dirty --format agent`; `record-rule` (glob `app/Services/LegacyImport/**`): "importadores legados idempotentes; mapa de ids em storage/app/legacy-id-map.json".

## Critérios de aceite

- [x] `legacy:import` roda limpo e é idempotente (2ª execução: 0 importados)
- [x] Contagens batem (skips justificados: 65 items deletados, 1 user/1 company de outra empresa, logo → setting)
- [x] `legacy-id-map.json` gerado (persistido em `finally`, mesmo em falha parcial)
- [x] Login funciona com senha legada de um usuário migrado (teste com hash do dump)
- [x] Testes passando (12 testes, 67 assertions)

## Registro de execução

_(preencher ao executar)_
