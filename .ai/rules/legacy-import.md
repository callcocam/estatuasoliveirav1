---
paths:
  - 'app/Services/LegacyImport/**'
---

# Legacy Import

## Import legado: deleted_at só em models com SoftDeletes
persistWithTimestamps só grava deleted_at quando o model usa SoftDeletes (class_uses_recursive). quote_items NÃO tem soft delete: itens do legado com deleted_at preenchido são pulados no QuoteImporter (report skip "deletado no legado") e não entram no total da quote.

## Mapa uuid→ulid é a fonte de idempotência do legacy:import
O import só reconhece registros já importados via storage/app/private/legacy-id-map.json (persistido em finally, mesmo em falha parcial). Se o mapa se perder com o banco populado, users caem em "e-mail já em uso" e quotes ficam com user_id nulo — nesse caso, rode migrate:fresh --seed + rm do mapa/relatório e reimporte. QuoteImporter::existingUser valida o ULID contra o banco (whereKey exists), não só contra o mapa.
