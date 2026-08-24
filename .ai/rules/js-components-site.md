---
paths:
  - 'app/Support/LegalContent.php,lang/pt_BR/app/site.php,resources/js/components/site/LegalPage.vue'
---

# Js Components Site

## Páginas legais: texto padrão vem de lang + LegalContent
Termos (/termos-de-uso) e Privacidade (/politica-de-privacidade) exibem o texto salvo em Setting content_terms/content_privacy quando existir; caso contrário caem no texto padrão LGPD definido em lang/pt_BR/app/site.php (bloco app.site.legal) e resolvido por App\Support\LegalContent::for('terms'|'privacy'), que substitui :company e :contact com dados de CompanyProfile. Ambas as páginas Vue reutilizam components/site/LegalPage.vue. A URL antiga /termos-e-politica tem redirect 301 em routes/web.php. Ao alterar seções, atualize as contagens em StaticPagesTest (9 termos / 12 privacidade).
