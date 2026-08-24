---
paths:
  - '**'
---

# General

## Comandos artisan/composer rodam no container Docker
O PHP local não tem driver pgsql. Rode tudo via `docker compose exec -T php php artisan ...` e `docker compose exec -T php vendor/bin/pint --format agent app database tests` (o diretório não é repo git, então `--dirty` não funciona). Postgres do stack: serviço `postgres`, exposto no host em 5434.

## Symlink public/storage aponta para /var/www (container)
`public/storage` → `/var/www/storage/app/public` é válido dentro dos containers (app montado em /var/www); no host WSL aparece quebrado — não "consertar" para caminho do host. O DemoSeeder baixa imagens reais de https://estatuasoliveira.com.br (storage/products e storage/sliders) para `storage/app/public/media/{products,sliders}`, com cache em disco (não rebaixa se o arquivo existe) e fallback sem mídia quando offline.
