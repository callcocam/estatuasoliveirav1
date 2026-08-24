<?php

namespace App\Services\LegacyImport;

use Illuminate\Support\Facades\Storage;

/**
 * Collects source/destination counts and skipped rows for the final report.
 */
class LegacyImportReport
{
    public const STORAGE_PATH = 'legacy-import-report.json';

    /** @var array<string, array{source: int, imported: int, skipped: int}> */
    private array $counts = [];

    /** @var list<array{table: string, id: string|null, reason: string}> */
    private array $skipped = [];

    /** @var list<string> */
    private array $notes = [];

    public function source(string $table, int $count): void
    {
        $this->counts[$table] ??= ['source' => 0, 'imported' => 0, 'skipped' => 0];
        $this->counts[$table]['source'] += $count;
    }

    public function imported(string $table): void
    {
        $this->counts[$table] ??= ['source' => 0, 'imported' => 0, 'skipped' => 0];
        $this->counts[$table]['imported']++;
    }

    public function skip(string $table, ?string $id, string $reason): void
    {
        $this->counts[$table] ??= ['source' => 0, 'imported' => 0, 'skipped' => 0];
        $this->counts[$table]['skipped']++;
        $this->skipped[] = ['table' => $table, 'id' => $id, 'reason' => $reason];
    }

    public function note(string $note): void
    {
        $this->notes[] = $note;
    }

    /**
     * @return array<string, array{source: int, imported: int, skipped: int}>
     */
    public function counts(): array
    {
        return $this->counts;
    }

    /**
     * @return list<array{table: string, id: string|null, reason: string}>
     */
    public function skippedRows(): array
    {
        return $this->skipped;
    }

    /**
     * @return list<string>
     */
    public function notes(): array
    {
        return $this->notes;
    }

    /**
     * Persist the full report to storage.
     */
    public function persist(): void
    {
        Storage::disk('local')->put(self::STORAGE_PATH, (string) json_encode([
            'generated_at' => now()->toIso8601String(),
            'counts' => $this->counts,
            'skipped' => $this->skipped,
            'notes' => $this->notes,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
