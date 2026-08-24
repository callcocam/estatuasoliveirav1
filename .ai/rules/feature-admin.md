---
paths:
  - 'resources/js/components/AppSidebar.vue,resources/js/components/admin/AdminSidebar.vue,tests/Feature/Admin/AdminAccessTest.php'
---

# Feature Admin

## AppSidebar (cliente) × AdminSidebar (admin) e matriz de acesso testada
Dois sidebars distintos: AppSidebar.vue é a área do cliente (AppLayout — Meus orçamentos p/ customer OU Painel p/ admin+manager, + Perfil/Segurança/Aparência, footer "Voltar ao site"; é role-aware porque as páginas de settings são compartilhadas entre papéis — não duplicar páginas). AdminSidebar.vue é só do painel admin (AdminLayout). Ambos usam AppLogo, que mostra logo (/images/logo.png) + nome da empresa do prop Inertia `site` (useCompany/CompanyProfile via settings) — sem sobras do starter kit (NavFooter/AppHeader/AppHeaderLayout foram removidos). A matriz de permissão Customer×admin (todas as rotas GET admin → 403, media.store POST → 403, guest → login, Manager × subgrupo :admin → 403) está coberta por tests/Feature/Admin/AdminAccessTest.php via dataset 'admin get routes' — rota admin nova entra nesse dataset.
