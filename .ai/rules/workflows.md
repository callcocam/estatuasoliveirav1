---
paths:
  - '.github/workflows/**,vps-deployment/**'
---

# Workflows

## Secrets de deploy ficam no environment "production"
O job de deploy usa `environment: production`; APP_HOST/APP_USER/SSH_PRIVATE_KEY são secrets de ENVIRONMENT (não de repo). A VPS de produção deste projeto é 187.77.255.84 (srv1774510), usuário root, chave ~/.ssh/id_ed25519_estatuasoliveirav1_deploy. NUNCA apontar para 148.230.78.184 — essa é a VPS do plannerate (outro projeto). Se o scp falhar com "create folder ... status 1", suspeite de host/usuário errado nos secrets.
