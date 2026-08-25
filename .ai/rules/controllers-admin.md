---
paths:
  - 'app/Http/Controllers/Admin/**'
---

# Controllers Admin

## Padrão CRUD admin (crud-laravel-inertia)
CRUDs admin seguem o padrão do skill crud-laravel-inertia (Products é a referência):
- Controllers usam os Concerns InteractsWithDeferredIndex (renderDeferredIndex + resolvePerPage whitelist 10/15/25/50), InteractsWithTrashedFilter (param `trashed` = without|only|with, nunca status=trashed) e InteractsWithResourceAbilities (prop `can` para a UI).
- Autorização via policies: ContentPolicy base (Admin+Manager via User::canManageContent()) estendida por Product/Category/Slider/Quote/ContactMessage; UserPolicy só Admin. Métodos de policy omitem o argumento do model de propósito (checks por role + can() em nível de classe). Base Controller já usa AuthorizesRequests.
- Form Requests separados: {Model}StoreRequest com authorize() e {Model}UpdateRequest estendendo-o (só muda authorize + regra unique com ignore).
- destroy em 2 estágios: soft delete; se já trashed, apaga arquivos de media + forceDelete e redireciona para index?trashed=only (rota resource com ->withTrashed(['destroy'])).
- Testes de props deferidas: `->assertInertia(fn ($page) => $page->loadDeferredProps(fn ($page) => ...))` (o método fica no AssertableInertia, não no TestResponse).
