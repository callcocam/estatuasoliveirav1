<?php

namespace App\Services\LegacyImport;

use App\Models\Product;

class ProductImporter extends LegacyImporter
{
    public function handle(): void
    {
        $rows = $this->reader->rows('products');
        $this->report->source('products', count($rows));

        foreach ($this->activeFirst($rows) as $row) {
            if (! $this->isRealCompany($row)) {
                $this->report->skip('products', $row['id'], 'outra empresa');

                continue;
            }

            $ulid = $this->ids->ulid('products', (string) $row['id']);

            if (Product::withTrashed()->whereKey($ulid)->exists()) {
                $this->report->skip('products', $row['id'], 'já importado');

                continue;
            }

            $customProperties = array_filter([
                'legacy_size' => $row['width'],
            ]);

            $product = new Product([
                'category_id' => $this->ids->existing('categories', $row['category_id']),
                'name' => trim((string) $row['name']),
                'slug' => $this->uniqueSlug(Product::class, (string) $row['slug'], $ulid),
                'reference' => $row['reference'],
                'description' => $row['description'],
                'status' => $this->publishStatus($row['status']),
                'featured' => $row['featured'] === 'yes',
                'height_cm' => $this->parseHeight($row['width'], (string) $row['id']),
                'stock' => $this->parseStock($row['stoke'], (string) $row['id']),
                'custom_properties' => $customProperties === [] ? null : $customProperties,
                'sort_order' => 0,
            ]);

            $product->id = $ulid;

            $this->persistWithTimestamps($product, $row);
            $this->report->imported('products');
        }
    }

    /**
     * Parse legacy sizes such as "Altura 60cm", "Altura 1m" or "Altura 1,30cm".
     */
    private function parseHeight(?string $value, string $legacyId): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        if (! preg_match('/(\d+(?:[.,]\d+)?)\s*(cm|m)?/iu', $value, $matches)) {
            $this->report->note("products {$legacyId}: medida legada não interpretada: \"{$value}\"");

            return null;
        }

        $number = (float) str_replace(',', '.', $matches[1]);
        $unit = strtolower($matches[2] ?? '');

        // Values like "1,30cm" actually mean meters; treat small numbers as meters.
        if ($unit === 'm' || $number < 10) {
            $number *= 100;
        }

        return (int) round($number);
    }

    /**
     * Parse the legacy varchar stock into an integer (fallback 0).
     */
    private function parseStock(?string $value, string $legacyId): int
    {
        if ($value === null || trim($value) === '') {
            return 0;
        }

        if (! ctype_digit(trim($value))) {
            $this->report->note("products {$legacyId}: stoke não numérico: \"{$value}\" → 0");

            return 0;
        }

        return (int) trim($value);
    }
}
