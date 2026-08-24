<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function __invoke(): Response
    {
        $images = Inertia::scroll(fn () => Media::query()
            ->where('mediable_type', Product::class)
            ->whereIn('mediable_id', Product::query()->published()->select('id'))
            ->with('mediable')
            ->latest()
            ->paginate(24)
            ->through(fn (Media $media): array => [
                'id' => $media->id,
                'url' => $media->url(),
                'productName' => $media->mediable?->name,
                'productSlug' => $media->mediable?->slug,
            ]));

        return Inertia::render('site/Gallery', [
            'images' => $images,
        ]);
    }
}
