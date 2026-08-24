<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    /**
     * Base URL of the legacy site used as source for demo images.
     */
    private const LEGACY_BASE_URL = 'https://estatuasoliveira.com.br';

    /**
     * Legacy pages scraped for product and slider image URLs.
     *
     * @var list<string>
     */
    private const LEGACY_SOURCE_PAGES = ['/', '/estatuas/budas/categories'];

    /**
     * Seed fake data for local development.
     */
    public function run(): void
    {
        ['products' => $productImageUrls, 'sliders' => $sliderImageUrls] = $this->scrapeLegacyImageUrls();

        $productImages = $this->downloadImages($productImageUrls, 'media/products');
        $sliderImages = $this->downloadImages($sliderImageUrls, 'media/sliders');

        if ($productImages === []) {
            $this->command?->warn('Nenhuma imagem de produto baixada de '.self::LEGACY_BASE_URL.' — produtos serão criados sem mídia.');
        }

        $categories = collect(['Estátuas', 'Budas', 'Vasos', 'Fontes', 'Bancos e Mesas'])
            ->map(fn (string $name, int $index) => Category::factory()
                ->published()
                ->create(['name' => $name, 'slug' => str($name)->slug(), 'sort_order' => $index]));

        $nextImage = 0;

        $categories->each(function (Category $category) use ($productImages, &$nextImage) {
            Product::factory()
                ->count(6)
                ->published()
                ->for($category)
                ->create()
                ->each(function (Product $product) use ($productImages, &$nextImage) {
                    $this->attachImages($product, $productImages, 2, $nextImage);
                });
        });

        Product::query()->inRandomOrder()->limit(8)->update(['featured' => true]);

        $nextSliderImage = 0;

        Slider::factory()->count(3)->published()->sequence(
            ['sort_order' => 0],
            ['sort_order' => 1],
            ['sort_order' => 2],
        )->create()->each(function (Slider $slider) use ($sliderImages, &$nextSliderImage) {
            $this->attachImages($slider, $sliderImages, 1, $nextSliderImage);
        });

        Quote::factory()
            ->count(5)
            ->has(QuoteItem::factory()->count(2), 'items')
            ->create()
            ->each(fn (Quote $quote) => $quote->recalculateTotal());

        ContactMessage::factory()->count(5)->create();
        ContactMessage::factory()->count(3)->read()->create();
    }

    /**
     * Scrape the legacy site pages for product and slider image URLs.
     *
     * @return array{products: list<string>, sliders: list<string>}
     */
    private function scrapeLegacyImageUrls(): array
    {
        $urls = ['products' => [], 'sliders' => []];

        foreach (self::LEGACY_SOURCE_PAGES as $page) {
            $response = Http::timeout(15)->get(self::LEGACY_BASE_URL.$page);

            if ($response->failed()) {
                continue;
            }

            preg_match_all(
                '#https?://[^"\'\s]+/storage/(products|sliders)/[^"\'\s]+\.(?:jpe?g|png|webp)#i',
                $response->body(),
                $matches
            );

            foreach ($matches[0] as $index => $url) {
                $urls[strtolower($matches[1][$index])][] = $url;
            }
        }

        return array_map(fn (array $list): array => array_values(array_unique($list)), $urls);
    }

    /**
     * Download the given image URLs to the public disk, skipping files already present.
     *
     * @param  list<string>  $urls
     * @return list<array{path: string, file_name: string, mime_type: string, size: int}>
     */
    private function downloadImages(array $urls, string $directory): array
    {
        $disk = Storage::disk('public');
        $images = [];

        foreach ($urls as $url) {
            $fileName = basename((string) parse_url($url, PHP_URL_PATH));
            $path = $directory.'/'.$fileName;

            if (! $disk->exists($path)) {
                $response = Http::timeout(30)->get($url);

                if ($response->failed()) {
                    $this->command?->warn("Falha ao baixar {$url} — imagem ignorada.");

                    continue;
                }

                $disk->put($path, $response->body());
            }

            $images[] = [
                'path' => $path,
                'file_name' => $fileName,
                'mime_type' => $disk->mimeType($path) ?: 'image/jpeg',
                'size' => (int) $disk->size($path),
            ];
        }

        return $images;
    }

    /**
     * Attach media records cycling through the downloaded image pool.
     *
     * @param  Model&object{media: mixed}  $model
     * @param  list<array{path: string, file_name: string, mime_type: string, size: int}>  $images
     */
    private function attachImages(Model $model, array $images, int $count, int &$nextImage): void
    {
        if ($images === []) {
            return;
        }

        for ($sortOrder = 0; $sortOrder < $count; $sortOrder++) {
            $image = $images[$nextImage % count($images)];
            $nextImage++;

            $model->media()->create([
                'collection' => 'default',
                'disk' => 'public',
                'sort_order' => $sortOrder,
                ...$image,
            ]);
        }
    }
}
