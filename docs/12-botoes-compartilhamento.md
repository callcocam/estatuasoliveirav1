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

_(preencher ao executar)_
