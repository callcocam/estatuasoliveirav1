---
paths:
  - 'resources/js/pages/site/**'
---

# Site

## Convenções das páginas públicas do site
Páginas públicas usam SiteLayout (resources/js/layouts/SiteLayout.vue) que já emite <Head> com title/description/og — passe title obrigatório. Cores/raios só via tokens site-* (bg-site-surface, text-site-on-surface, rounded-site[-card|-badge]); headlines com font-display. Dados da empresa vêm da prop compartilhada `site` via useCompany() (App\Support\CompanyProfile lê settings). Cards de produto: shape de App\Support\ProductPresenter::card + componente site/ProductCard.vue. Listagens longas usam Inertia::scroll + <InfiniteScroll>; props pesadas Inertia::defer com skeleton animado. Textos sempre via t('app.site.*') (lang/pt_BR/app/site.php).
