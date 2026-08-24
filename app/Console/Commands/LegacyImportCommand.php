<?php

namespace App\Console\Commands;

use App\Services\LegacyImport\CategoryImporter;
use App\Services\LegacyImport\LegacyIdMap;
use App\Services\LegacyImport\LegacyImporter;
use App\Services\LegacyImport\LegacyImportReport;
use App\Services\LegacyImport\MediaImporter;
use App\Services\LegacyImport\ProductImporter;
use App\Services\LegacyImport\QuoteImporter;
use App\Services\LegacyImport\SettingsImporter;
use App\Services\LegacyImport\SliderImporter;
use App\Services\LegacyImport\UserImporter;
use App\Support\LegacySqlReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LegacyImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:import
        {--fresh : Apaga os dados de domínio importáveis antes de importar}
        {--path=database/estatuas_db.sql : Caminho do dump legado}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa os dados do dump legado (MySQL/UUID) para o schema novo (ULID)';

    /**
     * The importers, in dependency order.
     *
     * @var list<class-string<LegacyImporter>>
     */
    private const IMPORTERS = [
        SettingsImporter::class,
        CategoryImporter::class,
        ProductImporter::class,
        SliderImporter::class,
        MediaImporter::class,
        UserImporter::class,
        QuoteImporter::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $reader = new LegacySqlReader(base_path((string) $this->option('path')));
        $ids = LegacyIdMap::load();
        $report = new LegacyImportReport;

        if ($this->option('fresh')) {
            $this->freshen($ids);
        }

        try {
            foreach (self::IMPORTERS as $importerClass) {
                $importer = new $importerClass($reader, $ids, $report);

                DB::transaction(fn () => $importer->handle());
            }
        } finally {
            // Persiste mesmo em falha parcial: sem o mapa uuid→ulid, uma nova
            // execução não reconhece os registros já importados.
            $ids->persist();
            $report->persist();
        }

        $this->table(
            ['Tabela legada', 'Origem', 'Importados', 'Pulados'],
            collect($report->counts())->map(fn (array $count, string $table) => [
                $table, $count['source'], $count['imported'], $count['skipped'],
            ]),
        );

        foreach ($report->notes() as $note) {
            $this->line("  • {$note}");
        }

        $this->info('Relatório: storage/app/'.LegacyImportReport::STORAGE_PATH);
        $this->info('Mapa de ids: storage/app/'.LegacyIdMap::STORAGE_PATH);

        return self::SUCCESS;
    }

    /**
     * Remove previously imported domain rows (keeps seeded/local users intact).
     */
    private function freshen(LegacyIdMap $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach (['media', 'quote_items', 'quotes', 'products', 'categories', 'sliders'] as $table) {
                DB::table($table)->delete();
            }

            $legacyUserIds = $ids->ulids('users');

            if ($legacyUserIds !== []) {
                DB::table('users')->whereIn('id', $legacyUserIds)->delete();
            }
        });
    }
}
