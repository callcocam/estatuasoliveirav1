---
paths:
  - 'resources/js/composables/useCompany.ts,resources/js/components/site/**,resources/js/layouts/auth/**'
---

# Auth

## Links de WhatsApp sempre com mensagem identificando o site
useCompany() expõe whatsappUrlWithMessage: wa.me com texto pré-preenchido de app.site.whatsapp.default_message (placeholders :company/:url — a URL vem de company.url, compartilhada pelo backend em CompanyProfile::toArray()). Header/footer/FAB e novos links de WhatsApp devem usar whatsappUrlWithMessage (ou WhatsAppButton, que já cai nele sem prop message) — nunca whatsappUrl cru sem texto. O sufixo do título das abas vem de resolveAppName() em app.ts (nome da empresa do payload Inertia inicial, fallback VITE_APP_NAME).
