# Estátuas Oliveira

Site institucional e catálogo de produtos da **Estátuas Oliveira** — empresa com mais de 25 anos fabricando estátuas, vasos, fontes e decorações em cimento. Não há checkout: o orçamento é feito por WhatsApp, telefone ou formulário de contato.

## O que o site tem

- **Site público**: Home, Nossa História, Galeria de produtos, Contato e Termos.
- **Painel admin**: gestão de produtos, categorias, sliders, mensagens de contato e configurações da empresa.
- **Dois temas visuais** (Serene Stone e Terracotta Earth), com troca em tempo real.
- **Botões de compartilhamento**: em cada produto (e no lightbox da galeria) é possível compartilhar via WhatsApp, Facebook, Instagram ou copiar o link.
- **Botão de contato via WhatsApp**: presente no site, abre conversa direta com o número da empresa. O número é configurado no admin (Configurações → WhatsApp).
- **Gerador de descrição com IA**: no formulário de produto do admin, o botão "Gerar descrição" cria automaticamente um texto comercial a partir do nome, categoria e medidas do produto. Você pode editar antes de salvar.

## O que precisa para o site funcionar

1. **Docker** instalado (o projeto roda em containers — PHP, Postgres, etc.).
2. Copiar o arquivo de ambiente e gerar a chave:
   ```bash
   cp .env.example .env
   docker compose up -d
   docker compose exec -T php php artisan key:generate
   ```
3. **Banco de dados** (criar tabelas e dados iniciais):
   ```bash
   docker compose exec -T php php artisan migrate --seed
   ```
4. **Frontend** (instalar e compilar):
   ```bash
   npm install
   npm run dev      # durante o desenvolvimento
   npm run build    # para produção
   ```

## Configurações importantes

- **WhatsApp da empresa**: definido no painel admin (Configurações). É o número usado no botão de contato e nos orçamentos.
- **IA para descrições**: precisa de uma chave de API no `.env`. Por padrão usa o Gemini (gratuito):
  ```env
  AI_DRIVER=gemini
  GEMINI_API_KEY=sua-chave-aqui
  ```
  Também é possível usar `groq` (grátis), `ollama` (local), `openai` ou `anthropic` — basta trocar o `AI_DRIVER` e informar a chave correspondente. Sem chave configurada, o botão de gerar descrição mostra um erro amigável; o resto do site funciona normalmente.

## Mais detalhes

- Os planos e o histórico do que já foi feito estão em [`docs/README.md`](docs/README.md).
- Instruções de deploy: [`docs/deploy.md`](docs/deploy.md) e pasta `vps-deployment/`.
