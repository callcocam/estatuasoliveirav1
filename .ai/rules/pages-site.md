---
paths:
  - 'app/Http/Controllers/Site/ContactController.php,resources/js/pages/site/Contact.vue'
---

# Pages Site

## Prefill do formulário de contato via ?produto={slug}
GET /contato aceita ?produto={slug}: ContactController::show monta a prop `prefill` (subject/message via app.site.contact.product_subject/product_message/product_label) apenas para produtos published; slug inválido => prefill null. No Contact.vue, subject/message usam v-model (refs iniciados do prefill) e são limpos no @success do Form — não voltar a usar reset-on-success para esses dois campos (inputs uncontrolled com :value seriam sobrescritos em re-renders). O botão "Solicitar orçamento" em products/Show.vue linka contact({ query: { produto: product.slug } }).
