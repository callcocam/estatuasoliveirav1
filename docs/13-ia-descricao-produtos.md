# 13 — Geração de Descrição de Produtos com IA

**Pré-requisitos**: plano 05 concluído. Leia `docs/00-contexto.md` e `.ai/rules/admin.md`. Comandos via `docker compose exec -T php ...`.

## Objetivo

No formulário de produto do admin (`resources/js/pages/admin/products/Form.vue`), um botão **"Gerar descrição"** que chama um agente de IA e preenche o campo `description` (editável antes de salvar). Provider **gratuito por padrão**, com troca para pago só por configuração.

## Estratégia de providers

Driver-based, trocável por `.env` — **sem** SDK/pacote novo (usar `Http` client do Laravel; se preferir o pacote `prism-php/prism`, precisa de aprovação explícita do usuário — regra do CLAUDE.md sobre dependências):

| Driver | Custo | Modelo sugerido | Observação |
|---|---|---|---|
| `gemini` (**default**) | Free tier generoso | `gemini-2.0-flash` | REST `generativelanguage.googleapis.com`, só `GEMINI_API_KEY` |
| `groq` | Free tier | `llama-3.3-70b-versatile` | API compatível com OpenAI |
| `ollama` | Grátis/local | `llama3.2` | Para dev offline; `OLLAMA_URL` |
| `openai` | Pago | `gpt-4o-mini` | Compatível também com outros hosts via `base_url` |
| `anthropic` | Pago | `claude-haiku-4-5-20251001` | Header `x-api-key` + `anthropic-version` |

Config nova `config/ai.php`: `driver`, e por driver `key`, `model`, `base_url`. Nada de chave hardcoded; adicionar variáveis comentadas no `.env.example` (`AI_DRIVER=gemini`, `GEMINI_API_KEY=`, ...).

## Desenho do backend

1. `app/Services/Ai/` — seguir convenções de Services existentes (`app/Services/LegacyImport/` como referência de estilo):
   - `ProductDescriptionGenerator` (orquestra: monta prompt, chama driver, valida resposta);
   - contrato `TextGenerator` (interface: `generate(string $prompt): string`);
   - drivers finos por provider (payload/headers/parse de cada API) — toda chamada com `Http::timeout(...)` e tratamento de erro amigável.
2. Prompt (pt-BR): recebe nome, categoria, dimensões (`width_cm`/`height_cm`/`weight_kg`), material/observações digitados. Instruir: descrição comercial de estátua artesanal, 2–3 parágrafos, sem inventar medidas/materiais não informados, sem markdown.
3. Rota admin: `POST admin/products/generate-description` (grupo já protegido por `EnsureUserIsAdmin`), FormRequest com os campos acima, `throttle:10,1`. Resposta JSON `{ description: string }`.
4. Sem fila: chamada síncrona com timeout curto (~20s); erro do provider → 422 com mensagem traduzida ("Não foi possível gerar a descrição, tente novamente").

## Desenho do frontend

- Botão "Gerar descrição" (ícone `Sparkles`) junto ao textarea `description` no `Form.vue` (create e edit compartilham o form).
- Usar **`useHttp`** do Inertia v3 (requisição JSON sem visita de página — skill `inertia-vue-development`): envia os campos atuais do form, estado `processing` ("Gerando..."), preenche `form.description` no sucesso, toast/inline error na falha.
- Se `description` já tem conteúdo, confirmar antes de sobrescrever.
- Traduções em `lang/pt_BR/app/admin.php` + espelho `en`.

## Testes (Pest, com `Http::fake()` — nunca bater na API real)

- Unit: cada driver monta request correto e parseia resposta (fixtures de payload por provider).
- Feature: endpoint gera descrição (driver fake/gemini com `Http::fake`), valida campos, 403 para Customer, 422 quando provider falha, throttle.
- `preventStrayRequests` já é padrão da suíte — os testes quebram se algum driver escapar do fake.

## Encerramento

Pint + Larastan + `php artisan test --compact` + `npm run build && npm run lint`. `record-rule` (glob `app/Services/Ai/**`): como adicionar um novo driver e a regra "toda chamada de IA passa por `TextGenerator` + `Http::fake` nos testes".

## Registro de execução

_(preencher ao executar)_
