---
paths:
  - 'app/Actions/Fortify/**,app/Models/User.php'
---

# Fortify Models

## Registro: role Customer explícito e MustVerifyEmail obrigatório
CreateNewUser deve passar 'role' => UserRole::Customer explicitamente: o default está só no banco, então o model recém-criado em memória teria role null e RoleRedirect::pathFor() estoura UnhandledMatchError no redirect pós-registro. User implementa MustVerifyEmail — o middleware 'verified' (rotas de quotes do cliente) e Features::emailVerification() do Fortify dependem disso; não remover a interface.
