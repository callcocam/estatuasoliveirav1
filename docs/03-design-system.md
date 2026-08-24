# 03 — Design System (dois temas selecionáveis)

> Pré-requisitos: nenhum (pode rodar em paralelo com o plano 01). Leia `docs/00-contexto.md`.

## Objetivo

Implementar os dois design systems do redesign como **temas trocáveis em runtime**, base para o site público (plano 04) e o admin (plano 05):

1. **`stone`** — *Serene Stone & Silver* (claro, pedra/prata, primária navy) — spec em `stitch_est_tuas_oliveira_ui_redesign/serene_stone_silver/DESIGN.md`, exemplos nas telas `*_navy_version`.
2. **`terracotta`** — *Zen Earth & Stone / Terracotta* (terroso, bege/terracota) — spec em `stitch_est_tuas_oliveira_ui_redesign/terracotta_earth/DESIGN.md`, exemplos nas telas `*_1`/`*_2`.

## Arquitetura de temas

- **Tokens CSS**: no CSS de entrada (Tailwind v4, `resources/css/app.css`), definir custom properties semânticas (`--color-surface`, `--color-surface-container`, `--color-primary`, `--color-on-primary`, `--color-outline`, etc.) sob `:root[data-theme='stone']` e `:root[data-theme='terracotta']`, extraindo os valores **dos dois DESIGN.md** (eles seguem nomenclatura Material-like: surface, surface-container-low/high, primary, on-primary, secondary-container, outline-variant...). Mapear os tokens no `@theme` do Tailwind v4 (`--color-*`) para gerar utilities (`bg-surface`, `text-primary`...). Ativar a skill `tailwindcss-development` antes.
- **Tipografia**: ambos usam **EB Garamond** (display/headlines) + **Manrope** (body/UI). Carregar via `@fontsource` local ou Google Fonts com preload; tokens `--font-display` / `--font-sans`. Escalas (headline 56/48px etc.) conforme DESIGN.md.
- **Troca de tema**:
  - Atributo `data-theme` no `<html>` (Blade root do Inertia lê valor inicial).
  - Prioridade: cookie do visitante > setting global `site.default_theme` (tabela `settings`; enquanto o plano 01 não existir, fallback para `config('site.default_theme')`).
  - Composable `useTheme()` (`resources/js/composables/useTheme.ts`): `theme`, `setTheme()` — grava cookie (1 ano) e atualiza `data-theme` sem reload.
  - Componente `ThemeSwitcher.vue` (toggle discreto com preview das duas paletas, acessível).
  - Middleware/props compartilhadas do Inertia expõem `theme` atual.
  - Sem flash de tema errado: o Blade root aplica `data-theme` server-side a partir do cookie.
- **Dark mode**: fora de escopo — os dois temas são claros; não misturar com `prefers-color-scheme`.

## Componentes base (usar `reka-ui` já instalado como primitivo quando couber)

Criar em `resources/js/components/ui-site/` (não conflitar com os componentes do scaffolding em `components/`): `Button` (variants primary/secondary/ghost conforme radius e estilos de cada DESIGN.md — atenção: radius difere entre temas, ex. pill vs. suave → tokenizar `--radius-*`), `Card`, `SectionHeading`, `Badge/Chip`, `Input/Textarea/Select` com labels, `Dialog`, `Divider`. Todos consomem apenas tokens semânticos — **nenhuma cor hardcoded**.

## Passos

1. Ler os dois `DESIGN.md` por completo e 2–3 `code.html` de cada tema para extrair a tabela de tokens real (cores hex, radius, sombras, spacing, tipografia). Consolidar em `resources/css/themes.css` (importado pelo `app.css`).
2. Implementar tokens + mapeamento `@theme` Tailwind v4.
3. Implementar `useTheme`, `ThemeSwitcher.vue`, ajuste do Blade root (`resources/views/app.blade.php`) e middleware `HandleInertiaRequests` (prop `theme`).
4. Fontes EB Garamond + Manrope.
5. Componentes base listados acima, com uma página interna de showcase `resources/js/pages/dev/DesignSystem.vue` (rota só em ambiente local) mostrando todos os componentes nos dois temas lado a lado — serve de validação visual.
6. **Testes**: Pest feature para o middleware/cookie (tema inicial, override por cookie, valor inválido cai no default); browser test (Pest v5 `visit()`) trocando o tema e verificando `data-theme`.
7. `vendor/bin/pint --dirty --format agent`; `record-rule` (glob `resources/js/**` + `resources/css/**`): "cores somente via tokens semânticos de tema; temas `stone`/`terracotta` via data-theme; componentes de site em ui-site/".

## Critérios de aceite

- [ ] `npm run build` limpo (typescript + vite)
- [ ] Showcase renderiza os dois temas sem cor hardcoded
- [ ] Troca de tema persiste (cookie) e não pisca no reload
- [ ] Testes passando

## Registro de execução

_(preencher ao executar)_
