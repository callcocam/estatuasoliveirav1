---
paths:
  - 'app/Models/**'
---

# Models

## Soft delete em todas as tabelas
Todas as tabelas de domínio têm deleted_at e os models usam SoftDeletes (inclusive Media, Setting, QuoteItem, Membership e TeamInvitation, adicionados em 2026_08_24_125147_add_soft_deletes_to_remaining_tables). Exceções de comportamento: MediaController@destroy e a limpeza diária de convites expirados (routes/console.php) usam forceDelete de propósito — mídia apaga o arquivo físico junto, convite expirado não precisa de lixeira. Como team_members é pivot, BelongsToMany não aplica o escopo do SoftDeletes: Team::members() e HasTeams::teams() filtram com ->wherePivotNull('deleted_at') — mantenha isso em qualquer nova relação via team_members.
