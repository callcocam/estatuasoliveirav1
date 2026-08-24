---
paths:
  - routes/web.php
---

# Routes

## Redirects 301 do site legado e cache do sitemap
As URLs públicas do site antigo (/historia, /estatuas, /estatua/{slug}/visualizar, /estatuas/{slug}/categories, /lancamentos, /informacoes, /orcamentos) têm redirects 301 fixos em routes/web.php cobertos por tests/Feature/Site/LegacyRedirectsTest.php — não remover ao reorganizar rotas. O POST /contato tem throttle:5,1 (testado). O sitemap é cacheado 1h sob SitemapController::CACHE_KEY ('site.sitemap.xml'); se admin passar a invalidar sitemap ao publicar produto, use essa constante. Model::preventLazyLoading está ativo fora de produção (AppServiceProvider) — N+1 novo estoura exceção nos testes.
