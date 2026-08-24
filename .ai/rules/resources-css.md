---
paths:
  - 'resources/css/**'
---

# Resources Css

## Dark mode do site via .dark[data-theme=...]
O modo escuro do site NÃO usa `dark:` nos templates: cada tema (stone/terracotta) tem um bloco `.dark[data-theme='...']` em resources/css/themes.css que redefine os tokens `--site-*` (superfícies dim⇢highest, on-* tone ~80-90, sombra a 40% em vez de 4%). `.dark` e `data-theme` coexistem no <html>: `.dark` vem do useAppearance (cookie `appearance`, anti-flash no app.blade.php), `data-theme` do useTheme (cookie `site_theme`) — dois eixos independentes (luminosidade × cor). Toggle do visitante: site/SiteAppearanceToggle.vue (reaproveita useAppearance; não criar mecanismo paralelo).
