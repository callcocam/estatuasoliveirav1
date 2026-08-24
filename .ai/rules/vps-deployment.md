---
paths:
  - 'vps-deployment/**'
---

# Vps Deployment

## VPS de produção correta: 187.77.255.84
A produção de estatuasoliveira.shop roda na VPS 187.77.255.84 (hostname srv1774510, alias SSH `estatuas-vps`, user root). NUNCA usar 148.230.78.184 — é de outros projetos (plannerate) e houve um deploy errado lá. Na VPS correta: Traefik compartilhado `traefik-global` (network externa, certresolver `letsencrypt`), Postgres no host acessado via `host.docker.internal`, stack em /opt/production/estatuasoliveirav1 (compose project `estatuasoliveira-production`). Imagem é buildada no próprio servidor a partir de Dockerfile.prod (src sincronizado em /opt/production/estatuasoliveirav1/src).

## Secrets do deploy apontam para 187.77.255.84 (root)
Os secrets do environment `production` (APP_HOST/APP_USER/SSH_PRIVATE_KEY) devem apontar para 187.77.255.84 com user root e a chave dedicada `callcocam/estatuas-deploy` (privada em ~/.ssh/estatuas-deploy/ na máquina do dev). Até 2026-08-24 apontavam para 148.230.78.184 (plannerate, user deploy) e os runs "verdes" na verdade deployavam no servidor errado. Se o deploy falhar com "Process exited with status 1" no scp, confira no auth.log de QUAL servidor o runner logou antes de mexer no workflow.
