<?php

namespace App\Services\LegacyImport;

use App\Models\Media;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Slider;

class MediaImporter extends LegacyImporter
{
    /**
     * Legacy morph types and their new counterparts (legacy table => model).
     *
     * @var array<string, array{table: string, model: class-string}>
     */
    private const MORPH_MAP = [
        'App\\Product' => ['table' => 'products', 'model' => Product::class],
        'App\\Slider' => ['table' => 'sliders', 'model' => Slider::class],
    ];

    public function handle(): void
    {
        $rows = $this->reader->rows('files');
        $this->report->source('files', count($rows));

        foreach ($rows as $row) {
            if (! $this->isRealCompany($row)) {
                $this->report->skip('files', $row['id'], 'outra empresa');

                continue;
            }

            $fileName = trim((string) $row['name']);
            $path = self::normalizePath($row['fullPath'] ?? null) ?? $fileName;
            $type = (string) $row['fileable_type'];

            if ($type === 'App\\Company') {
                Setting::set('branding_logo_path', $path, 'branding');
                $this->ids->ulid('files', (string) $row['id']);
                $this->report->skip('files', $row['id'], 'logo da empresa → setting branding_logo_path');

                continue;
            }

            $morph = self::MORPH_MAP[$type] ?? null;

            if ($morph === null) {
                $this->report->skip('files', $row['id'], "fileable_type não migrado: {$type}");

                continue;
            }

            $mediableId = $this->ids->existing($morph['table'], $row['fileable_id']);

            if ($mediableId === null || ! $morph['model']::withTrashed()->whereKey($mediableId)->exists()) {
                $this->report->skip('files', $row['id'], "{$morph['table']}: registro pai não migrado");

                continue;
            }

            $ulid = $this->ids->ulid('files', (string) $row['id']);

            if (Media::query()->whereKey($ulid)->exists()) {
                $this->report->skip('files', $row['id'], 'já importado');

                continue;
            }

            $media = new Media([
                'collection' => 'default',
                'disk' => 'public',
                'path' => $path,
                'file_name' => $fileName,
                'mime_type' => $row['fileType'] ?: self::mimeFromName($fileName),
                'size' => is_numeric($row['size']) ? (int) $row['size'] : null,
                'sort_order' => $row['assets'] === 'cover' ? 0 : 1,
                'custom_properties' => array_filter([
                    'legacy_dir' => $row['dir'],
                    'legacy_assets' => $row['assets'],
                    'width' => is_numeric($row['width']) ? (int) $row['width'] : null,
                    'height' => is_numeric($row['height']) ? (int) $row['height'] : null,
                ]) ?: null,
            ]);

            $media->id = $ulid;
            $media->mediable_type = $morph['model'];
            $media->mediable_id = $mediableId;

            $this->persistWithTimestamps($media, $row);
            $this->report->imported('files');
        }
    }

    /**
     * Normalize the legacy fullPath (e.g. "storage/products/202304/x.jpeg")
     * into a path relative to the public disk.
     */
    private static function normalizePath(?string $fullPath): ?string
    {
        if ($fullPath === null || trim($fullPath) === '') {
            return null;
        }

        return preg_replace('#^/?(storage/|dist/upload/images/)#', '', trim($fullPath, '/'));
    }

    /**
     * Infer the mime type from the file name extension.
     */
    private static function mimeFromName(string $fileName): ?string
    {
        return match (strtolower(pathinfo($fileName, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => null,
        };
    }
}
