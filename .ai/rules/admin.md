---
paths:
  - 'app/Http/Controllers/Admin/**,resources/js/pages/admin/**'
---

# Admin

## Convenções do painel admin
Rotas em routes/admin.php sob prefixo/nome `admin.`, protegidas por EnsureUserIsAdmin (Admin+Manager; `:admin` restringe a Admin — usado em Usuários/Configurações). Controllers finos com Form Requests em app/Http/Requests/Admin; slugs via App\Support\UniqueSlug; flash de sucesso com Inertia::flash('toast', ...) e strings em lang/pt_BR/app/admin.php (`app.admin.*`). Páginas Vue em resources/js/pages/admin com defineOptions({ layout: AdminLayout }); upload de imagens sempre pelo endpoint admin.media.* (alias mediable_type product|slider|category) usando o componente admin/MediaUploader.vue. Bindings: Category e Product resolvem por slug nas rotas; Slider/Quote/ContactMessage/User/Media por ULID.
