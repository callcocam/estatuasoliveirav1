---
paths:
  - 'app/Services/LegacyImport/**'
---

# Legacy Import

## Import legado: deleted_at só em models com SoftDeletes
persistWithTimestamps só grava deleted_at quando o model usa SoftDeletes (class_uses_recursive). quote_items NÃO tem soft delete: itens do legado com deleted_at preenchido são pulados no QuoteImporter (report skip "deletado no legado") e não entram no total da quote.

## Mapa uuid→ulid é a fonte de idempotência do legacy:import
O import só reconhece registros já importados via storage/app/private/legacy-id-map.json (persistido em finally, mesmo em falha parcial). Se o mapa se perder com o banco populado, users caem em "e-mail já em uso" e quotes ficam com user_id nulo — nesse caso, rode migrate:fresh --seed + rm do mapa/relatório e reimporte. QuoteImporter::existingUser valida o ULID contra o banco (whereKey exists), não só contra o mapa.

## legacy:import não baixa arquivos de mídia
O legacy:import só cria as linhas de media (paths normalizados tipo products/2023xx/... no disk public); os arquivos físicos precisam ser baixados do site legado https://estatuasoliveira.com.br (prefixo /storage/ para products/companies; sliders legados vinham de /dist/upload/images/). Em produção (VPS 187.77.255.84) isso foi feito via tinker iterando Media e salvando em Storage::disk(public); o logo fica fora de media, no setting branding_logo_path. No local, só as ~22 imagens do DemoSeeder existem — as demais faltam mesmo após import.

## legacy:import-media copia arquivos físicos (fonte local + fallback download)
O plano 06 foi concluído com `php artisan legacy:import-media {--dry-run} {--prune} {--source=}` (MediaFileMigrator). A fonte default é o espelho `storage/app/private/legacy-media` (rsync de /home/caltj/projects/estatuasoliveira/public/storage/{products,sliders,companies} no host — o container não enxerga o projeto legado); fallback: download de https://estatuasoliveira.com.br/storage/{path}. Para as 37 mídias que o media:rename-to-slug renomeou antes de existir arquivo, o path original é resolvido via dump database/estatuas_db.sql + legacy-id-map.json (param legacyPaths). Grava size/width/height/migrated_at no registro; relatório em storage/app/private/legacy-media-report.json; idempotente (migrated_at + arquivo presente ⇒ skip). Copia também o logo do setting branding_logo_path. NÃO renomeia para ULID: convenção final de path é a do media:rename-to-slug (só produtos ativos).
