---
paths:
  - 'app/Support/CompanyProfile.php,resources/views/app.blade.php,resources/js/composables/useCompany.ts'
---

# Composables

## Logo/ícone da marca vem das configurações via CompanyProfile
O logo/ícone do sistema é o setting `branding_logo_path` (upload em Configurações do site, disco public). CompanyProfile::uploadedLogoUrl() retorna a URL ou null; logoUrl() aplica fallback `/images/logo.png` e é exposto como `site.logoUrl` na prop compartilhada. Componentes Vue devem usar `company.logoUrl` (useCompany) — nunca hardcode `/images/logo.png`. O favicon em app.blade.php usa o upload quando existe; sem upload, cai nos estáticos public/favicon*.png|ico gerados de public/images/aba-ico-transparent.png.
