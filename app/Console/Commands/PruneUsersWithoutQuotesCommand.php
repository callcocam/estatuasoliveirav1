<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('users:prune-without-quotes
    {--dry-run : Apenas mostra quantos usuários seriam excluídos, sem excluir}')]
#[Description('Soft-deleta usuários clientes sem nenhum orçamento (dados de teste)')]
class PruneUsersWithoutQuotesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = $this->pruneableUsers();

        $total = $query->count();

        if ($this->option('dry-run')) {
            $this->info(__('app.admin.users.prune_dry_run', ['count' => $total]));

            return self::SUCCESS;
        }

        $query->chunkById(200, function ($users) {
            $users->each->delete();
        });

        $this->info(__('app.admin.users.pruned', ['count' => $total]));

        return self::SUCCESS;
    }

    /**
     * Build the query for customer users without any quote (including trashed quotes).
     *
     * @return Builder<User>
     */
    private function pruneableUsers(): Builder
    {
        return User::query()
            ->where('role', UserRole::Customer)
            ->whereDoesntHave('quotes', fn (Builder $quotes) => $quotes->withTrashed());
    }
}
