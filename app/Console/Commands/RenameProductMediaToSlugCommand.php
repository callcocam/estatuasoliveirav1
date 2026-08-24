<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('media:rename-to-slug
    {--dry-run : Apenas mostra o que seria renomeado, sem alterar nada}')]
#[Description('Renomeia as imagens dos produtos para usar o slug do produto (SEO)')]
class RenameProductMediaToSlugCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $renamed = 0;
        $skipped = 0;
        $missingFiles = 0;

        Product::query()
            ->with('media')
            ->chunkById(100, function ($products) use ($dryRun, &$renamed, &$skipped, &$missingFiles) {
                foreach ($products as $product) {
                    foreach ($product->media->values() as $index => $media) {
                        $result = $this->renameMedia($product, $media, $index, $dryRun);

                        match ($result) {
                            'renamed' => $renamed++,
                            'skipped' => $skipped++,
                            'missing' => $missingFiles++,
                        };
                    }
                }
            });

        $key = $dryRun ? 'app.admin.media.rename_dry_run' : 'app.admin.media.renamed';

        $this->info(__($key, [
            'renamed' => $renamed,
            'skipped' => $skipped,
            'missing' => $missingFiles,
        ]));

        return self::SUCCESS;
    }

    /**
     * Rename a single media file/record to the slug-based path.
     *
     * @return 'renamed'|'skipped'|'missing'
     */
    private function renameMedia(Product $product, Media $media, int $index, bool $dryRun): string
    {
        $extension = strtolower(pathinfo($media->path, PATHINFO_EXTENSION)) ?: 'jpg';
        $suffix = $index === 0 ? '' : '-'.($index + 1);
        $newFileName = $product->slug.$suffix.'.'.$extension;
        $newPath = 'products/'.$newFileName;

        if ($media->path === $newPath) {
            return 'skipped';
        }

        $disk = Storage::disk($media->disk);
        $fileExists = $disk->exists($media->path);

        if ($dryRun) {
            $this->line(($fileExists ? '' : '[arquivo ausente] ').$media->path.' -> '.$newPath);

            return $fileExists ? 'renamed' : 'missing';
        }

        if ($fileExists) {
            if ($disk->exists($newPath)) {
                $disk->delete($newPath);
            }

            $disk->move($media->path, $newPath);
        } else {
            $this->warn('[arquivo ausente] '.$media->path.' -> '.$newPath);
        }

        $media->update([
            'path' => $newPath,
            'file_name' => $newFileName,
        ]);

        return $fileExists ? 'renamed' : 'missing';
    }
}
