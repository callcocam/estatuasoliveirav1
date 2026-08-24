---
paths:
  - 'resources/js/**'
---

# Resources Js

## Lucide sem brand icons; compartilhamento via site/ShareButtons.vue
O @lucide/vue instalado NÃO tem ícones de marca (Facebook/Twitter/Instagram etc. foram removidos do lucide) — use SVG inline `fill="currentColor"`, como em site/WhatsAppButton.vue e site/ShareButtons.vue. Compartilhamento de produto: componente site/ShareButtons.vue (props url/title) — Web Share API detectada em onMounted (nunca no setup síncrono, evita mismatch de hidratação SSR) com fallback WhatsApp/Facebook/X/copiar link; estiliza com currentColor (border-current/30) para funcionar em superfície clara e no lightbox escuro. A URL canônica do produto vem do backend (prop product.url = route('products.show')).
