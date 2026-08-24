---
paths:
  - 'resources/views/vendor/mail/**,config/mail.php'
---

# Mail

## Branding de email centralizado no layout markdown publicado
Emails markdown usam o tema `estatuas` (config/mail.php → markdown.theme; CSS em resources/views/vendor/mail/html/themes/estatuas.css, derivado do default com acento #001d4a). O header (logo via CompanyProfile::absoluteLogoUrl()) e o footer (nome, endereço, telefone, email e link de WhatsApp com texto pré-preenchido) são montados em html/message.blade.php e text/message.blade.php chamando \App\Support\CompanyProfile direto no blade — renderiza na hora do envio (queue-safe, sem DB no boot). Mailables individuais NÃO devem repetir contatos/rodapé; só o corpo.
