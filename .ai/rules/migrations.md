---
paths:
  - 'database/migrations/**'
---

# Migrations

## Todas as PKs de tabelas próprias são ULID
Use $table->ulid('id')->primary() e foreignUlid() para FKs. users e todo o domínio (categories, products, media, sliders, quotes, quote_items, contact_messages, settings) já usam ULID. As tabelas de teams (teams, team_members, team_invitations) foram removidas em 2026_08_24_130000_drop_team_tables (migração irreversível). Models usam HasUlids; type hints de ids são ?string (nunca int). Status é string(20) com cast para enum (PublishStatus/QuoteStatus/UserRole).
