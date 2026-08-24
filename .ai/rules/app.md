---
paths:
  - 'app/Providers/AppServiceProvider.php,app/Support/CompanyProfile.php,lang/pt_BR/app/mail.php'
---

# App

## Notificações de auth e remetente levam a identidade do site
AppServiceProvider::bootAuthMailables() traduz/marca VerifyEmail e ResetPassword via toMailUsing (textos em lang/pt_BR/app/mail.php, placeholders :company/:name). bootMailFromName() escuta MessageSending e troca o display name do from para CompanyProfile::name() em todo email — não hardcode MAIL_FROM_NAME em Mailables. CompanyProfile expõe name(), absoluteLogoUrl() (email clients precisam de URL absoluta) e whatsappUrl(?string $text) que espelha o builder do frontend (useCompany.ts: prefixo 55 + wa.me + ?text= rawurlencode).
