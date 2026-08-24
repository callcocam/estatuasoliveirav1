# Planos de Execução — Refatoração Estátuas Oliveira

Refatoração completa do site **Estátuas Oliveira** (estatuasoliveira.com.br), migrando um projeto legado Laravel 6 (`/home/caltj/projects/estatuasoliveira`) para este projeto novo (`/home/caltj/projects/estatuasoliveirav1`).

## Como usar estes planos

Cada arquivo em `docs/` é **autocontido** — foi escrito para ser executado em um **chat separado**. Basta abrir um chat novo neste projeto e pedir: *"execute o plano docs/0X-nome.md"*. Cada plano contém o contexto necessário, mas **sempre leia este README e o `00-contexto.md` primeiro** se algo parecer ambíguo.

## Ordem de execução

| # | Plano | Depende de | Entrega | Status |
|---|-------|------------|---------|--------|
| 0 | [00-contexto.md](00-contexto.md) | — | Contexto compartilhado (não é executável) | — |
| 1 | [01-banco-de-dados.md](01-banco-de-dados.md) | — | Migrations ULID, models, factories, seeders | ✅ Concluído |
| 2 | [02-migracao-de-dados.md](02-migracao-de-dados.md) | 1 | Comando de importação do dump legado (UUID → ULID) | ✅ Concluído |
| 3 | [03-design-system.md](03-design-system.md) | — | Dois temas (Serene Stone / Terracotta Earth) com troca em runtime | ✅ Concluído |
| 4 | [04-site-publico.md](04-site-publico.md) | 1, 3 | Páginas públicas (Home, História, Galeria, Contato, Termos) | ✅ Concluído |
| 5 | [05-admin.md](05-admin.md) | 1, 3 | Painel administrativo completo | ✅ Concluído |
| 6 | [06-midias-e-arquivos.md](06-midias-e-arquivos.md) | 1, 2 | Migração física das imagens do projeto antigo | ✅ Concluído |
| 7 | [07-qualidade-e-deploy.md](07-qualidade-e-deploy.md) | todos | Cobertura de testes, revisão, preparação de deploy | ✅ Concluído (2026-08-24) |

Os planos 1, 3 podem rodar **em paralelo** (chats simultâneos). O plano 4 e 5 podem rodar em paralelo entre si depois que 1 e 3 terminarem.

## Regras globais (valem para todos os chats)

- **ULID em tudo**: toda PK é ULID (`HasUlids` + `$table->ulid('id')->primary()`). Nunca auto-increment, nunca UUID.
- **Stack**: Laravel 13 + PHP 8.5, Inertia v3 + Vue 3 + TypeScript, Tailwind CSS v4, Fortify, Wayfinder, Pest 5. Seguir `CLAUDE.md` (Boost guidelines) à risca.
- **Não adicionar dependências** sem necessidade real; se precisar, justifique no resumo final do chat.
- **Testes**: toda entrega inclui testes Pest (feature primeiro). Rodar `php artisan test --compact` filtrado antes de encerrar.
- **Pint**: rodar `vendor/bin/pint --dirty --format agent` ao final de qualquer alteração PHP.
- **Idioma**: UI em pt-BR; código (classes, métodos, variáveis) em inglês.
- Ao concluir um plano, marque os checkboxes no próprio arquivo do plano e anote desvios na seção "Registro de execução" ao final dele.
