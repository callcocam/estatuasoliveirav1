---
paths:
  - 'app/Http/Responses/**,app/Support/RoleRedirect.php,routes/web.php'
---

# Support

## Redirect pós-login é por papel (RoleRedirect), teams foi removido
O destino pós-autenticação é único e centralizado em App\Support\RoleRedirect::pathFor(): Admin/Manager → route('admin.dashboard'), Customer → route('quotes.index') (área "Meus orçamentos" em /meus-orcamentos, Customer\QuoteController). Todas as 5 responses do Fortify/Passkeys e o redirectUsersTo() em bootstrap/app.php usam esse helper — não hardcode caminhos de redirect. A rota nomeada 'dashboard' existe apenas como redirect por papel (fortify.home = /dashboard). O recurso de teams (models, rotas, middleware, páginas Vue, tabelas) foi removido em 2026-08-24; não reintroduza referências a currentTeam/HasTeams.
