<?php

namespace App\Services\LegacyImport;

use App\Models\Media;
use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Copies the legacy physical media files into the new public disk,
 * fixing size/dimensions on each `media` row.
 */
class MediaFileMigrator
{
    public const REPORT_PATH = 'legacy-media-report.json';

    private const PRODUCTION_BASE_URL = 'https://estatuasoliveira.com.br/storage/';

    /** @var list<array{id: string|null, path: string, action: string}> */
    private array $planned = [];

    /** @var list<array{id: string|null, path: string, reason: string}> */
    private array $missing = [];

    /** @var array{copied: int, downloaded: int, skipped: int, missing: int, pruned: int} */
    private array $counts = ['copied' => 0, 'downloaded' => 0, 'skipped' => 0, 'missing' => 0, 'pruned' => 0];

    /**
     * @param  array<string, string>  $legacyPaths  Fallback ulid => legacy relative path (from the dump).
     */
    public function __construct(
        private readonly string $sourcePath,
        private readonly bool $dryRun = false,
        private readonly bool $prune = false,
        private readonly array $legacyPaths = [],
    ) {}

    /**
     * Run the migration and return the summary counts.
     *
     * @return array{copied: int, downloaded: int, skipped: int, missing: int, pruned: int}
     */
    public function handle(): array
    {
        Media::query()->orderBy('id')->each(fn (Media $media) => $this->migrateMedia($media));

        $this->migrateBrandingLogo();

        if (! $this->dryRun) {
            $this->writeReport();
        }

        return $this->counts;
    }

    /**
     * @return list<array{id: string|null, path: string, action: string}>
     */
    public function planned(): array
    {
        return $this->planned;
    }

    /**
     * @return list<array{id: string|null, path: string, reason: string}>
     */
    public function missing(): array
    {
        return $this->missing;
    }

    private function migrateMedia(Media $media): void
    {
        $disk = Storage::disk($media->disk);
        $migratedAt = $media->custom_properties['migrated_at'] ?? null;

        if ($migratedAt !== null && $disk->exists($media->path)) {
            $this->counts['skipped']++;

            return;
        }

        [$contents, $origin] = $this->resolveContents($this->candidatePaths($media));

        if ($contents === null) {
            $this->registerMissing($media->id, $media->path);

            if ($this->prune && ! $this->dryRun) {
                $media->delete();
                $this->counts['pruned']++;
            }

            return;
        }

        $this->counts[$origin === 'download' ? 'downloaded' : 'copied']++;
        $this->planned[] = ['id' => $media->id, 'path' => $media->path, 'action' => $origin];

        if ($this->dryRun) {
            return;
        }

        $disk->put($media->path, $contents);

        $dimensions = @getimagesizefromstring($contents) ?: null;

        $media->update([
            'size' => strlen($contents),
            'custom_properties' => array_merge($media->custom_properties ?? [], array_filter([
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
            ]) + ['migrated_at' => now()->toIso8601String()]),
        ]);
    }

    private function migrateBrandingLogo(): void
    {
        $path = Setting::get('branding_logo_path');

        if (! is_string($path) || $path === '') {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            $this->counts['skipped']++;

            return;
        }

        [$contents, $origin] = $this->resolveContents([$path]);

        if ($contents === null) {
            $this->registerMissing(null, $path);

            return;
        }

        $this->counts[$origin === 'download' ? 'downloaded' : 'copied']++;
        $this->planned[] = ['id' => null, 'path' => $path, 'action' => $origin];

        if (! $this->dryRun) {
            $disk->put($path, $contents);
        }
    }

    /**
     * Paths under which the file may exist in the legacy source: the current
     * path plus the original legacy path (for rows renamed after import).
     *
     * @return list<string>
     */
    private function candidatePaths(Media $media): array
    {
        return array_values(array_unique(array_filter([
            $media->path,
            $this->legacyPaths[$media->id] ?? null,
        ])));
    }

    /**
     * Resolve the file contents from the local legacy mirror or, as a
     * fallback, from the production site.
     *
     * @param  list<string>  $paths
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveContents(array $paths): array
    {
        foreach ($paths as $path) {
            $sourceFile = rtrim($this->sourcePath, '/').'/'.$path;

            if (is_file($sourceFile)) {
                return [(string) file_get_contents($sourceFile), 'copy'];
            }
        }

        foreach ($paths as $path) {
            try {
                $response = Http::timeout(30)->get(self::PRODUCTION_BASE_URL.$path);
            } catch (ConnectionException) {
                continue;
            }

            if ($response->successful() && $response->body() !== '') {
                return [$response->body(), 'download'];
            }
        }

        return [null, null];
    }

    private function registerMissing(?string $id, string $path): void
    {
        $this->counts['missing']++;
        $this->missing[] = ['id' => $id, 'path' => $path, 'reason' => 'arquivo não encontrado no legado nem na produção'];
    }

    private function writeReport(): void
    {
        Storage::disk('local')->put(self::REPORT_PATH, (string) json_encode([
            'generated_at' => now()->toIso8601String(),
            'source_path' => $this->sourcePath,
            'counts' => $this->counts,
            'missing' => $this->missing,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
