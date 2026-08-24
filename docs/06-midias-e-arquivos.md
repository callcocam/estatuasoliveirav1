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

- [ ] Imagens acessíveis via disk `public` nos caminhos novos com ULID
- [ ] Relatório sem faltas não justificadas
- [ ] Site exibe imagens reais de produtos e sliders
- [ ] Testes passando

## Registro de execução

_(preencher ao executar)_
