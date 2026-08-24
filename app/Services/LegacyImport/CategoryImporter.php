<?php

namespace App\Services\LegacyImport;

use App\Models\Category;

class CategoryImporter extends LegacyImporter
{
    public function handle(): void
    {
        $rows = $this->reader->rows('categories');
        $this->report->source('categories', count($rows));

        foreach ($this->activeFirst($rows) as $row) {
            if (! $this->isRealCompany($row)) {
                $this->report->skip('categories', $row['id'], 'outra empresa');

                continue;
            }

            $ulid = $this->ids->ulid('categories', (string) $row['id']);

            if (Category::withTrashed()->whereKey($ulid)->exists()) {
                $this->report->skip('categories', $row['id'], 'já importada');

                continue;
            }

            $category = new Category([
                'name' => trim((string) $row['name']),
                'slug' => $this->uniqueSlug(Category::class, (string) $row['slug'], $ulid),
                'description' => $row['description'],
                'status' => $this->publishStatus($row['status']),
                'sort_order' => 0,
            ]);

            $category->id = $ulid;

            $this->persistWithTimestamps($category, $row);
            $this->report->imported('categories');
        }
    }
}
