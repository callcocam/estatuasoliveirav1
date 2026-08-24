---
paths:
  - 'app/Console/Commands/**,storage/app/public/**'
---

# Public

## Imagens de produto usam path com slug (media:rename-to-slug)
O comando `media:rename-to-slug` renomeia arquivos e registros de media de produtos para `products/{slug}.{ext}` (extras: `{slug}-2.{ext}`, numerados por sort_order) — motivo: SEO/URLs legíveis. É idempotente; atualiza o registro mesmo sem arquivo físico (no local os arquivos não existem) e foi aplicado em produção (VPS 187.77.255.84) em 2026-08-24. Rode-o após qualquer reimport do legado (legacy:import recria paths tipo products/2023xx/...). Uploads novos pelo admin NÃO passam por esse padrão automaticamente.
