---
paths:
  - 'app/Services/Ai/**'
---

# Ai

## Drivers de IA: TextGenerator + Http::fake
Toda chamada de IA passa pelo contrato App\Services\Ai\TextGenerator, resolvido pelo TextGeneratorFactory a partir de config/ai.php (AI_DRIVER; default gemini, gratuito). Para adicionar um provider novo: criar um driver fino estendendo Drivers\HttpTextDriver (implementar send() com Laravel Http + textPath() com o caminho dot-notation do texto na resposta), registrar o case no match do TextGeneratorFactory e o bloco em config/ai.php (key/model/base_url via env, nada hardcoded). Sem SDKs/pacotes de IA — só o Http client. Nos testes, sempre Http::fake() + Http::preventStrayRequests() (nunca bater na API real); cobrir request montado e parse (ver tests/Feature/Services/AiDriverTest.php). Erros de provider viram TextGenerationFailedException, que o endpoint admin.products.generate-description converte em 422 com app.admin.products.ai.failed.
