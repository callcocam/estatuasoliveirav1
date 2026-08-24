# 07 — Qualidade, Revisão e Deploy

> Pré-requisito: planos 01–06 concluídos. Leia `docs/00-contexto.md`.

## Objetivo

Fechar o projeto: cobertura de testes, análise estática, revisão, performance e preparação de deploy.

## Passos

### 1. Qualidade de código
- `vendor/bin/pint --format agent` (repo inteiro).
- Larastan (`vendor/bin/phpstan analyse`) — corrigir até nível configurado no `phpstan.neon` (elevar para nível 6+ se viável).
- `npm run build` + eslint/vue-tsc sem erros.

### 2. Testes
- Suíte completa: `php artisan test --compact`. Cobrir lacunas óbvias: policies, scopes `published()`, comandos legacy (`--fresh`, `--prune`), mailable de contato.
- Browser tests (Pest 5): fluxos críticos — visitante navega catálogo e envia contato nos dois temas; admin cria produto com imagem e publica.
- Smoke test de todas as rotas GET públicas e admin (sem erros JS/500).

### 3. Revisão
- Rodar a skill `code-review` (nível high) sobre o estado final; aplicar correções relevantes.
- Rodar a skill `security-review`: atenção a upload de arquivos (mime/size/path traversal), autorização admin, mass assignment, honeypot/ratelimit do formulário de contato (adicionar `throttle` se faltou).

### 4. Performance
- N+1: verificar listagens (produtos + media, quotes + items) com eager loading; `Model::preventLazyLoading` em non-production.
- Cache: settings cacheados (plano 01), sitemap cacheado, headers de cache para imagens do storage.
- Imagens: conferir `srcset`/lazy loading nas páginas públicas; se thumbs ficaram pendentes (plano 05), decidir com o usuário se adiciona dependência de processamento de imagem.

### 5. Deploy (preparação — deploy real depende de decisão do usuário)
- `.env.example` atualizado (mail, ADMIN_INITIAL_PASSWORD, queue, disk).
- Documentar em `docs/deploy.md`: passos de provisionamento (migrate, `legacy:import`, `legacy:import-media`, `storage:link`, build, queue worker, scheduler), recomendação Laravel Cloud como caminho rápido.
- Conferir `config/app.php` locale `pt_BR`/timezone `America/Sao_Paulo`.
- Redirects 301 das URLs antigas relevantes (ex.: rotas do site legado → novas) num middleware/rotas de fallback — levantar as URLs do legado (`routes/web.php` do projeto antigo) antes.

### 6. Encerramento
- Atualizar `docs/README.md` marcando planos concluídos.
- `record-rule` para qualquer decisão durável tomada aqui.

## Critérios de aceite

- [ ] Suíte de testes completa verde
- [ ] Larastan e eslint sem erros
- [ ] Revisões (code + security) aplicadas
- [ ] `docs/deploy.md` criado
- [ ] Redirects 301 das URLs legadas mapeados

## Registro de execução

_(preencher ao executar)_
