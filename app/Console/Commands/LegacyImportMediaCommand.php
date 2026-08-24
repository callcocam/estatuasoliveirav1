<?php

namespace App\Console\Commands;

use App\Services\LegacyImport\LegacyIdMap;
use App\Services\LegacyImport\MediaFileMigrator;
use App\Support\LegacySqlReader;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('legacy:import-media
    {--dry-run : Mostra o plano de cópia sem tocar em nada}
    {--prune : Remove registros de media cujo arquivo não foi encontrado}
    {--source=storage/app/private/legacy-media : Espelho local do public/storage legado}')]
#[Description('Copia os arquivos físicos de mídia do legado para o disk public')]
class LegacyImportMediaCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = (string) $this->option('source');

        if (! str_starts_with($source, '/')) {
            $source = base_path($source);
        }

        if (! is_dir($source)) {
            $this->warn("Fonte local não encontrada ({$source}); usando apenas o fallback de download da produção.");
        }

        $migrator = new MediaFileMigrator(
            sourcePath: $source,
            dryRun: (bool) $this->option('dry-run'),
            prune: (bool) $this->option('prune'),
            legacyPaths: $this->legacyPathsFromDump(),
        );

        $counts = $migrator->handle();

        if ($this->option('dry-run')) {
            foreach ($migrator->planned() as $item) {
                $this->line("[{$item['action']}] {$item['path']}");
            }
        }

        foreach ($migrator->missing() as $item) {
            $this->warn("[ausente] {$item['path']}");
        }

        $this->info(sprintf(
            'Copiados: %d | Baixados: %d | Já migrados: %d | Ausentes: %d | Removidos: %d',
            $counts['copied'],
            $counts['downloaded'],
            $counts['skipped'],
            $counts['missing'],
            $counts['pruned'],
        ));

        return self::SUCCESS;
    }

    /**
     * Map media ULIDs to their original legacy paths using the dump and the
     * persisted id map, for rows whose path was renamed after the import.
     *
     * @return array<string, string>
     */
    private function legacyPathsFromDump(): array
    {
        $dump = base_path('database/estatuas_db.sql');

        if (! is_file($dump)) {
            return [];
        }

        $ids = LegacyIdMap::load();
        $paths = [];

        foreach ((new LegacySqlReader($dump))->rows('files') as $row) {
            $ulid = $ids->existing('files', (string) $row['id']);
            $fullPath = trim((string) ($row['fullPath'] ?? ''), '/');

            if ($ulid === null || $fullPath === '') {
                continue;
            }

            $paths[$ulid] = (string) preg_replace('#^(storage/|dist/upload/images/)#', '', $fullPath);
        }

        return $paths;
    }
}
