---
paths:
  - 'app/Http/Controllers/Admin/**,resources/js/pages/admin/**'
---

# Admin

## Convenções do painel admin
Rotas em routes/admin.php sob prefixo/nome `admin.`, protegidas por EnsureUserIsAdmin (Admin+Manager; `:admin` restringe a Admin — usado em Usuários/Configurações). Controllers finos com Form Requests em app/Http/Requests/Admin; slugs via App\Support\UniqueSlug; flash de sucesso com Inertia::flash('toast', ...) e strings em lang/pt_BR/app/admin.php (`app.admin.*`). Páginas Vue em resources/js/pages/admin com defineOptions({ layout: AdminLayout }); upload de imagens sempre pelo endpoint admin.media.* (alias mediable_type product|slider|category) usando o componente admin/MediaUploader.vue. Bindings: Category e Product resolvem por slug nas rotas; Slider/Quote/ContactMessage/User/Media por ULID.

## Padrão de filtro Excluídos + restore nas listas admin
Toda lista do admin segue o padrão de products: query com withTrashed() + when(filtro==='trashed', whereNotNull deleted_at) e whereNull por padrão; cada linha expõe 'deleted' => $model->trashed(); rota POST {resource}/{model}/restore com ->withTrashed(); no Vue, badge t('app.admin.common.deleted_badge'), opção de filtro t('app.admin.common.filter_trashed') e botão RotateCcw que faz router.post no restore (linha excluída esconde ações de edição). Users/quotes/sliders/messages já implementam; siga o mesmo padrão em listas novas. Comando artisan users:prune-without-quotes (--dry-run) soft-deleta customers sem nenhum orçamento (quotes com withTrashed contam como "tem orçamento").
