---
paths:
  - 'database/migrations/**'
---

# Migrations

## Todas as PKs de tabelas próprias são ULID
Use $table->ulid('id')->primary() e foreignUlid() para FKs. users, teams, team_members, team_invitations e todo o domínio (categories, products, media, sliders, quotes, quote_items, contact_messages, settings) já usam ULID. Models usam HasUlids; type hints de ids são ?string (nunca int). Status é string(20) com cast para enum (PublishStatus/QuoteStatus/UserRole).
