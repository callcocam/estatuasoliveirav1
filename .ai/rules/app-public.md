---
paths:
  - 'app/Services/LegacyImport/**,storage/app/public/**'
---

# App Public

## Storage local sincronizado com a produção (169 arquivos)
Em 2026-08-24 os 169 arquivos de media (products + sliders) foram copiados da VPS (187.77.255.84, container estatuasoliveira-production-app-1, via tar por ssh) para storage/app/public local — substitui a nota antiga de que só as ~22 imagens do DemoSeeder existiam. Local e produção têm os mesmos registros de media (37 de produtos ativos com path slug via media:rename-to-slug; o resto são paths legados de produtos soft-deletados e sliders). Para ressincronizar: ssh estatuas-vps 'docker exec estatuasoliveira-production-app-1 tar -cf - -C /var/www/storage/app/public products sliders' | tar -xf - -C storage/app/public/
