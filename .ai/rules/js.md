---
paths:
  - 'lang/**,resources/js/**'
---

# Js

## Arquitetura de traduções (docs/traducoes-arquitetura.md)
Backend: locale pt_BR, fallback en. Arquivos por grupo em lang/pt_BR/app/*.php mesclados pelo MergingFileLoader (App\Support\Translation) sob a chave `app.*`; strings do framework em lang/pt_BR/*.php e pt_BR.json. Frontend: composable useT() lê a árvore `translations` compartilhada via HandleInertiaRequests — nunca hardcode texto visível em componentes Vue; use t('app.grupo.chave'). Testes devem asserir mensagens via __() e nunca strings em inglês fixas.
