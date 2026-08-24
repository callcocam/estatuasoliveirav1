<?php

namespace App\Support;

use App\Models\Product;

class ProductPresenter
{
    /**
     * Shape a product for the public catalog cards.
     *
     * @return array<string, mixed>
     */
    public static function card(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'reference' => $product->reference,
            'categoryName' => $product->category?->name,
            'image' => $product->coverMedia()?->url(),
            'widthCm' => $product->width_cm,
            'heightCm' => $product->height_cm,
        ];
    }
}
