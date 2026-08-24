<?php

namespace App\Services\LegacyImport;

use App\Enums\PublishStatus;
use App\Support\LegacySqlReader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

abstract class LegacyImporter
{
    /**
     * UUID of the real company in the legacy dump; other tenants are ignored.
     */
    public const REAL_COMPANY_ID = '3c4624d9-42e1-4ddb-b39c-5f6d95e8c93f';

    public function __construct(
        protected LegacySqlReader $reader,
        protected LegacyIdMap $ids,
        protected LegacyImportReport $report,
    ) {
        //
    }

    /**
     * Run the import for the importer's legacy table.
     */
    abstract public function handle(): void;

    /**
     * Sort rows so active records import first and keep their original slug
     * when there is a collision with a soft-deleted record.
     *
     * @param  list<array<string, string|null>>  $rows
     * @return list<array<string, string|null>>
     */
    protected function activeFirst(array $rows): array
    {
        usort($rows, fn (array $a, array $b) => ($a['deleted_at'] !== null) <=> ($b['deleted_at'] !== null));

        return $rows;
    }

    /**
     * Determine if the row belongs to the real company.
     *
     * @param  array<string, string|null>  $row
     */
    protected function isRealCompany(array $row): bool
    {
        return ($row['company_id'] ?? null) === self::REAL_COMPANY_ID;
    }

    /**
     * Convert a legacy status string into the publish status enum.
     */
    protected function publishStatus(?string $status): PublishStatus
    {
        return match ($status) {
            'published' => PublishStatus::Published,
            default => PublishStatus::Draft,
        };
    }

    /**
     * Persist the model preserving the legacy timestamps and soft delete.
     *
     * @param  array<string, string|null>  $row
     */
    protected function persistWithTimestamps(Model $model, array $row): void
    {
        $model->timestamps = false;

        $model->forceFill(array_filter([
            'created_at' => self::timestamp($row['created_at'] ?? null),
            'updated_at' => self::timestamp($row['updated_at'] ?? null),
        ]));

        $isDeleted = ($row['deleted_at'] ?? null) !== null || ($row['status'] ?? null) === 'deleted';

        if ($isDeleted && in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
            $model->forceFill(['deleted_at' => self::timestamp($row['deleted_at'] ?? null) ?? now()]);
        }

        $model->save();
    }

    /**
     * Generate a slug that does not collide with existing rows (including trashed).
     *
     * @param  class-string<Model>  $model
     */
    protected function uniqueSlug(string $model, string $slug, string $ulid): string
    {
        $candidate = $slug;
        $suffix = 1;

        while ($model::withTrashed()->where('slug', $candidate)->whereKeyNot($ulid)->exists()) {
            $candidate = $slug.'-'.(++$suffix);
        }

        return $candidate;
    }

    /**
     * Parse a legacy timestamp string.
     */
    protected static function timestamp(?string $value): ?Carbon
    {
        return $value ? Carbon::parse($value) : null;
    }
}
