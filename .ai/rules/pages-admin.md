---
paths:
  - 'resources/js/pages/admin/**'
---

# Pages Admin

## Listas admin: componentes compartilhados
Páginas de lista do admin usam a fundação criada na refatoração (products/Index.vue é a referência):
- ListPage (header + tabela + skeleton + vazio + AdminPagination), ListFiltersBar (busca com debounce 400ms, selects de trashed/per_page, slot scoped {values, set} para filtros extras; sentinela 'all' para selects, 'without'/''/valores default são removidos da query), ColumnActions (EditButton/DeleteButton/RestoreButton; DeleteButton exige digitar palavra de confirmação de resources/js/support/confirmWords.ts e usa `permanent` quando o item está na lixeira).
- Paginator vem deferido: prop opcional + useDeferredPaginator(() => props.x) para isLoading/isEmpty/rows/links.
- Tipos em resources/js/types/admin.ts (Paginated, ResourceAbilities, TrashedFilter, {Model}Row).
- Ícones sempre de '@lucide/vue' (NÃO 'lucide-vue-next' — quebra o vue-tsc).
