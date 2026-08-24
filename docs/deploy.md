# Deploy — Estátuas Oliveira

O deploy real deste projeto é feito na **VPS 187.77.255.84** (`estatuas-vps`, produção de `estatuasoliveira.shop`). Toda a automação vive em `vps-deployment/` — leia `vps-deployment/README.md` e `.ai/rules/vps-deployment.md` antes de mexer. Este documento resume o provisionamento da aplicação em qualquer ambiente novo.

## Pipeline atual (GitHub Actions)

| Workflow | Gatilho | Função |
|---|---|---|
| `.github/workflows/tests.yml` | push/PR | Pint + Larastan + Pest + build front |
| `.github/workflows/vps-build-push.yml` | push em `main` | Build da imagem (`Dockerfile.prod`) |
| `.github/workflows/vps-deploy-production.yml` | após build | Deploy na VPS (`/opt/production/estatuasoliveirav1`) |
| `.github/workflows/vps-rollback.yml` | manual | Rollback para tag anterior |

## Provisionamento de um ambiente do zero

1. **Variáveis** — copie `.env.example` e preencha:
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `APP_KEY` (`php artisan key:generate`)
   - `APP_TIMEZONE=America/Sao_Paulo`, `APP_LOCALE=pt_BR` (já são os defaults)
   - Banco Postgres (conexão única), `QUEUE_CONNECTION=redis`, Redis
   - Mail SMTP real (`MAIL_*`) — o formulário de contato envia `ContactMessageReceived` em fila
   - `ADMIN_INITIAL_PASSWORD` — senha do admin inicial (`contato@estatuasoliveira.com.br`); se vazio, o seeder gera uma aleatória e imprime no console
   - `SITE_DEFAULT_THEME=stone` (ou `terracotta`)
2. **Migrations + seed**:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=AdminUserSeeder --force
   ```
3. **Importação do legado** (uma vez, com o dump e as mídias disponíveis):
   ```bash
   php artisan legacy:import --path=database/legacy/dump.sql
   php artisan legacy:import-media           # baixa/copia imagens (use --prune p/ órfãs)
   ```
4. **Storage + build**:
   ```bash
   php artisan storage:link
   npm ci && npm run build
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
5. **Processos**: além do web server, rodar
   - queue worker: `php artisan queue:work redis` (sem Horizon)
   - scheduler: `php artisan schedule:run` a cada minuto (cron)

## Alternativa rápida

[Laravel Cloud](https://cloud.laravel.com/) cobre web + queue + scheduler + Postgres gerenciado sem manter a VPS — opção válida se a VPS for descontinuada.

## SEO / URLs legadas

Redirects 301 do site antigo estão em `routes/web.php` (`/historia`, `/estatuas`, `/estatua/{slug}/visualizar`, `/estatuas/{slug}/categories`, `/lancamentos`, `/informacoes`, `/orcamentos`). O sitemap (`/sitemap.xml`) é cacheado por 1 hora (`SitemapController::CACHE_KEY`).

## Checklist pós-deploy

- [ ] `/up` responde 200
- [ ] Login admin funciona e `ADMIN_INITIAL_PASSWORD` foi trocada
- [ ] Envio do formulário de contato chega no e-mail configurado (`contact_email` em Configurações)
- [ ] Imagens de produtos/sliders carregam (storage link + mídias importadas)
- [ ] `php artisan queue:work` ativo (e-mails saem da fila)
