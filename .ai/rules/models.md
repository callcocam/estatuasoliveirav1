---
paths:
  - 'app/Models/**'
---

# Models

## Soft delete em todas as tabelas
Todas as tabelas de domínio têm deleted_at e os models usam SoftDeletes (inclusive Media, Setting e QuoteItem, adicionados em 2026_08_24_125147_add_soft_deletes_to_remaining_tables). Exceção de comportamento: MediaController@destroy usa forceDelete de propósito — mídia apaga o arquivo físico junto. O recurso de teams foi removido (models, tabelas e rotas) em 2026-08-24.
