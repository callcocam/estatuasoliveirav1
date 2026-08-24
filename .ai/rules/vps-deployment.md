---
paths:
  - 'vps-deployment/**'
---

# Vps Deployment

## VPS de produção correta: 187.77.255.84
A produção de estatuasoliveira.shop roda na VPS 187.77.255.84 (hostname srv1774510, alias SSH `estatuas-vps`, user root). NUNCA usar 148.230.78.184 — é de outros projetos (plannerate) e houve um deploy errado lá. Na VPS correta: Traefik compartilhado `traefik-global` (network externa, certresolver `letsencrypt`), Postgres no host acessado via `host.docker.internal`, stack em /opt/production/estatuasoliveirav1 (compose project `estatuasoliveira-production`). Imagem é buildada no próprio servidor a partir de Dockerfile.prod (src sincronizado em /opt/production/estatuasoliveirav1/src).
