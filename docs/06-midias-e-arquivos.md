# 06 — Migração de Mídias (arquivos físicos)

> Pré-requisitos: planos 01 e 02 (precisa de `storage/app/legacy-id-map.json` e da tabela `media` populada). Leia `docs/00-contexto.md`.

## Objetivo

Copiar as imagens físicas do projeto legado para o storage novo, renomeando caminhos para a convenção nova (ULID) e corrigindo os registros `media`.

## Fonte dos arquivos

Projeto legado: `/home/caltj/projects/estatuasoliveira/public/` — os caminhos do banco legado eram tipo `/dist/upload/images/...` e URLs públicas `storage/products/202304/...` (conferir onde os arquivos realmente estão: inspecionar `public/dist/upload`, `public/storage`, `storage/app/public` do legado antes de codar; registrar o layout real encontrado).

## Estratégia

Comando Artisan idempotente `php artisan legacy:import-media {--dry-run}`:

1. Para cada registro `media` importado no plano 02, resolver o arquivo físico no legado a partir do `path` original (guardar o path legado em `custom_properties->legacy_path` já no plano 02; se não foi feito, derivar do dump + `legacy-id-map.json`).
2. Copiar para o disk `public` novo: `products/{ulid}/{filename}` (slug do nome + extensão), `sliders/{ulid}/...`, logo → `branding/`.
3. Atualizar `media.path`, `size`, `width/height` reais (recalcular com getimagesize), marcar `custom_properties->migrated_at`.
4. Arquivo não encontrado → listar no relatório (`storage/app/legacy-media-report.json`) e **não** falhar o comando; ao final, opção de remover registros `media` órfãos (`--prune`).
5. `--dry-run` mostra o plano de cópia sem tocar em nada.
6. Rodar `php artisan storage:link` se necessário.

## Fallback com as imagens do redesign

A pasta `stitch_est_tuas_oliveira_ui_redesign/image_from_https_estatuasoliveira.com.br_storage_*/` contém screenshots de produtos/sliders do site em produção — **não** usar como fonte primária (são screenshots, não originais). Se um arquivo não existir no legado, tentar baixar o original da URL de produção (`https://estatuasoliveira.com.br/storage/...`, derivável do nome da pasta) antes de desistir.

## Passos

1. Inspecionar layout real de arquivos do legado; documentar no registro de execução.
2. Implementar o comando + service (`app/Services/LegacyImport/MediaFileMigrator.php`).
3. **Testes Pest** com `Storage::fake` e arquivos fixture: cópia, renomeio, atualização de registro, dry-run não altera nada, órfão vai pro relatório, idempotência.
4. Executar de verdade (`--dry-run` primeiro, revisar, depois real). Validar amostra: abrir 3–4 URLs de imagem no site (plano 04) e conferir renderização.
5. `vendor/bin/pint --dirty --format agent`.

## Critérios de aceite

- [x] Imagens acessíveis via disk `public` nos caminhos migrados
- [x] Relatório sem faltas não justificadas
- [x] Site exibe imagens reais de produtos e sliders
- [x] Testes passando

## Registro de execução

- **2026-08-24** — Concluído.
- Layout real do legado: arquivos em `/home/caltj/projects/estatuasoliveira/public/storage/{products,sliders,companies}` (166 + 23 + 28 arquivos), paths do banco novo já espelhavam esses caminhos relativos (`products/202005/...`). `public/dist/upload` não continha originais relevantes.
- Como o artisan roda no container (sem acesso ao projeto legado no host), a fonte é um espelho local: `rsync -a .../estatuasoliveira/public/storage/{products,sliders,companies} storage/app/private/legacy-media/` e o comando lê de `--source=storage/app/private/legacy-media` (default).
- Decisão de convenção: **não** renomeamos para `products/{ulid}/...` — o comando copia para o `media.path` atual e o renomeio SEO fica com `php artisan media:rename-to-slug` (`products/{slug}[-n].{ext}`, só produtos ativos). 37 mídias já estavam com path de slug (rename rodou antes dos arquivos existirem); o migrator resolve o arquivo original via dump + `legacy-id-map.json` (`legacyPaths`).
- Execução: `legacy:import-media --dry-run` → 170 cópias planejadas, 0 ausentes; execução real copiou 169 mídias + logo (`companies/202006/83144932-1591191622-logo-new.png`); segunda execução: 170 "já migrados" (idempotente). Relatório em `storage/app/private/legacy-media-report.json`.
- Validação: URLs `/storage/products/...`, `/storage/sliders/...`, `/storage/companies/...` retornando 200 via nginx; `media:rename-to-slug` depois da cópia: 37 já corretas, 0 sem arquivo.
- Testes: `tests/Feature/Console/LegacyImportMediaCommandTest.php` (8 testes: cópia+metadados, dry-run, download de produção, ausente→relatório, `--prune`, idempotência, fallback de path legado, logo).
