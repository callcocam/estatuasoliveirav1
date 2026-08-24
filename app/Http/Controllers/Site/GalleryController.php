<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function __invoke(): Response
    {
        $images = Inertia::scroll(fn () => $this->galleryImages());

        return Inertia::render('site/Gallery', [
            'images' => $images,
        ]);
    }

    /**
     * Paginate the published product images for the public gallery.
     *
     * @return LengthAwarePaginator<int, covariant array<string, mixed>>
     */
    private function galleryImages(): LengthAwarePaginator
    {
        return Media::query()
            ->where('mediable_type', Product::class)
            ->whereIn('mediable_id', Product::query()->published()->select('id'))
            ->with('mediable')
            ->latest()
            ->paginate(24)
            ->through(function (Media $media): array {
                /** @var Product|null $product */
                $product = $media->mediable;

                return [
                    'id' => $media->id,
                    'url' => $media->url(),
                    'productName' => $product?->name,
                    'productSlug' => $product?->slug,
                ];
            });
    }
}
