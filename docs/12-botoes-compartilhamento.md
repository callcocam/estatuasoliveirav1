# 12 — Botões de Compartilhamento

**Pré-requisitos**: plano 04 concluído. Leia `docs/00-contexto.md`. Comandos via `docker compose exec -T php ...`.

## Objetivo

Permitir compartilhar produtos (e a galeria) da forma **mais simples possível** — sem SDKs de terceiros, sem scripts externos, sem dependência nova:

- **Web Share API nativa** (`navigator.share`) quando disponível (mobile cobre a maioria).
- Fallback com links diretos (abrem em nova aba, são só URLs — zero JS de terceiros):
  - WhatsApp: `https://wa.me/?text={título + url}` (público-alvo BR, o site já usa WhatsApp para orçamento)
  - Facebook: `https://www.facebook.com/sharer/sharer.php?u={url}`
  - X/Twitter: `https://twitter.com/intent/tweet?url={url}&text={título}`
  - **Copiar link** (`navigator.clipboard.writeText` + feedback "copiado!")

## Contexto do código

- Página alvo principal: `resources/js/pages/site/products/Show.vue` (detalhe do produto). Secundária: card/lightbox da Galeria, se ficar barato.
- Já existe `resources/js/components/site/WhatsAppButton.vue` — ver como monta URL/estilo antes de criar componente novo (convenção: reusar padrões existentes).
- Estilo: somente utilities `site-*`; ícones via `@lucide/vue` (já instalado — `Share2`, `Link`, `Facebook`, `Twitter`; para WhatsApp reaproveitar o ícone/abordagem do `WhatsAppButton`).
- A URL canônica do produto: `route('products.show', slug)` já vem nas props/`usePage().url` — usar URL absoluta (props do backend ou `window.location.href`).
- O SEO/OG das páginas de produto (title, og:image) já foi feito no plano 04 — é o que dá preview bonito ao compartilhar; validar que segue ok.
- Traduções em `lang/pt_BR/app/site.php` + espelho `en` ("Compartilhar", "Copiar link", "Link copiado!").

## Passos

1. Criar `resources/js/components/site/ShareButtons.vue`:
   - props: `url`, `title`;
   - se `navigator.share` existir → um botão único "Compartilhar";
   - senão → linha de botões (WhatsApp, Facebook, X, Copiar link);
   - sem estado global, sem pinia, sem lib.
2. Usar em `products/Show.vue` (perto do título/CTA de orçamento).
3. (Opcional, se trivial) usar no lightbox da Galeria apontando para o produto vinculado.
4. Testes: componente é client-side puro; cobrir o que é server-side (props de URL absoluta se adicionadas ao controller) + `npm run lint`/`vue-tsc` limpos. Feature test existente de products.show continua verde.

## Encerramento

`php artisan test --compact` (filtrado em Site) + `npm run build && npm run lint`. Pint se tocar PHP.

## Registro de execução

**Executado em 2026-08-24.**

- Criado `resources/js/components/site/ShareButtons.vue` (props `url`/`title`): botões **sempre visíveis** — WhatsApp (`wa.me/?text=`), Facebook sharer, Instagram e Copiar link (`navigator.clipboard` + feedback "Link copiado!" com `role="status"`, reset em 2s). Sem estado global, sem libs novas. Decisão pós-review com o usuário: a Web Share API foi descartada (no desktop escondia os botões de marca atrás de um único botão nativo); o Instagram não tem URL de compartilhamento web com link pré-preenchido, então o botão copia o link e abre instagram.com em nova aba. A mensagem do WhatsApp inclui identidade da empresa via `useCompany()` (nome + tagline + "Fale conosco: :phone" quando há WhatsApp/telefone nos settings) — chaves `app.site.share.message`/`contact`.
- Ícones: `Share2`/`Link`/`Check` do `@lucide/vue`; **Facebook/Twitter não existem mais no pacote instalado** (lucide removeu brand icons) → SVGs inline, mesmo padrão do `WhatsAppButton.vue` (inclusive reutilizando o path do ícone do WhatsApp).
- Estilo só com tokens/utilities: botões usam `currentColor` (`border-current/30`, `hover:opacity-70`) para se adaptarem tanto à superfície clara do produto quanto ao lightbox escuro (`site-inverse-surface`) da galeria; em `products/Show.vue` a cor é dada pelo wrapper (`text-site-on-surface-variant`).
- URL absoluta via backend: prop `url` (`route('products.show', $product)`) adicionada em `Site\ProductController@show` + tipo `SiteProductDetail`; coberta em `tests/Feature/Site/ProductShowTest.php`.
- Uso em `products/Show.vue` (abaixo do CTA de orçamento) e no lightbox de `Gallery.vue` (produto vinculado; URL montada com `window.location.origin` + Wayfinder — lightbox só renderiza no cliente).
- Traduções em `lang/pt_BR/app/site.php` grupo `share` (button/whatsapp/facebook/twitter/copy/copied). Não existe espelho `lang/en` no projeto (arquitetura usa só pt_BR com fallback en do framework) — nada a espelhar.
- OG/SEO validado: `SiteLayout` já emite `og:title`/`og:description`/`og:image` (plano 04) — preview de compartilhamento ok.
- Verificações: Pint passed · `php artisan test --compact tests/Feature/Site` 32 passed (271 assertions) · `npm run lint` limpo · `vue-tsc --noEmit` limpo · `npm run build` ok.
