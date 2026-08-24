<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Cache key for the rendered sitemap XML.
     */
    public const CACHE_KEY = 'site.sitemap.xml';

    public function __invoke(): Response
    {
        $xml = Cache::remember(self::CACHE_KEY, now()->addHour(), fn (): string => $this->buildXml());

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Build the full sitemap XML for public pages and published products.
     */
    private function buildXml(): string
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('about'), 'priority' => '0.6'],
            ['loc' => route('products.index'), 'priority' => '0.9'],
            ['loc' => route('gallery'), 'priority' => '0.6'],
            ['loc' => route('contact'), 'priority' => '0.6'],
            ['loc' => route('terms'), 'priority' => '0.3'],
            ['loc' => route('privacy'), 'priority' => '0.3'],
        ]);

        $productUrls = Product::query()
            ->published()
            ->orderBy('slug')
            ->get(['slug', 'updated_at'])
            ->map(fn (Product $product): array => [
                'loc' => route('products.show', $product),
                'lastmod' => $product->updated_at?->toAtomString(),
                'priority' => '0.8',
            ]);

        $entries = $urls->concat($productUrls)->map(function (array $url): string {
            $lastmod = isset($url['lastmod']) ? "<lastmod>{$url['lastmod']}</lastmod>" : '';

            return '<url><loc>'.e($url['loc']).'</loc>'.$lastmod."<priority>{$url['priority']}</priority></url>";
        })->implode('');

        return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$entries.'</urlset>';
    }
}
