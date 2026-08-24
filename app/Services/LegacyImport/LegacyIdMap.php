<?php

namespace App\Services\LegacyImport;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Dictionary of legacy UUIDs to new ULIDs, persisted for later plans (file migration).
 */
class LegacyIdMap
{
    public const STORAGE_PATH = 'legacy-id-map.json';

    /**
     * @param  array<string, array<string, string>>  $map
     */
    public function __construct(private array $map = [])
    {
        //
    }

    /**
     * Load the persisted map from storage, when present.
     */
    public static function load(): static
    {
        $contents = Storage::disk('local')->exists(self::STORAGE_PATH)
            ? Storage::disk('local')->get(self::STORAGE_PATH)
            : null;

        /** @var array<string, array<string, string>> $map */
        $map = $contents ? (json_decode($contents, true) ?: []) : [];

        return new static($map);
    }

    /**
     * Get (or generate and memoize) the ULID for a legacy UUID.
     */
    public function ulid(string $table, string $uuid): string
    {
        return $this->map[$table][$uuid] ??= strtolower((string) Str::ulid());
    }

    /**
     * Get the ULID only if the UUID was already mapped.
     */
    public function existing(string $table, ?string $uuid): ?string
    {
        return $uuid === null ? null : ($this->map[$table][$uuid] ?? null);
    }

    /**
     * Get all mapped ULIDs for a legacy table.
     *
     * @return list<string>
     */
    public function ulids(string $table): array
    {
        return array_values($this->map[$table] ?? []);
    }

    /**
     * Persist the map to storage for reuse by later imports and plans.
     */
    public function persist(): void
    {
        Storage::disk('local')->put(
            self::STORAGE_PATH,
            (string) json_encode($this->map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        );
    }
}
