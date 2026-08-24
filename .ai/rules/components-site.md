---
paths:
  - 'app/Http/Middleware/HandleInertiaRequests.php,resources/js/types/auth.ts,resources/js/components/site/SiteUserMenu.vue'
---

# Components Site

## Shape enxuto do auth.user compartilhado via Inertia
HandleInertiaRequests compartilha `auth.user` como array explícito (id, name, email, role, email_verified_at) ou null para guest — nunca o model User inteiro (evita vazar phone/two_factor_*). O tipo TS `Auth.user` é `User | null`; componentes de páginas autenticadas usam `page.props.auth.user!`. Pontos de entrada de auth no site público vivem em site/SiteUserMenu.vue (guest: Entrar/Criar conta; autenticado: dropdown com Meus orçamentos p/ customer, Painel p/ admin+manager, Sair via POST logout). O link "Área do cliente" do rodapé aponta sempre para route('login') — usuário autenticado é redirecionado por RoleRedirect (redirectUsersTo), sem duplicar lógica de papel no front.
