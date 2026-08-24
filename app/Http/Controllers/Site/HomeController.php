<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slider;
use App\Support\ProductPresenter;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $sliders = Slider::query()
            ->published()
            ->with('media')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Slider $slider): array => [
                'id' => $slider->id,
                'title' => $slider->title,
                'subtitle' => $slider->subtitle,
                'description' => $slider->description,
                'ctaLabel' => $slider->cta_label,
                'ctaUrl' => $slider->cta_url,
                'image' => $slider->coverMedia()?->url(),
            ]);

        $featuredProducts = Product::query()
            ->published()
            ->featured()
            ->with(['media', 'category'])
            ->orderBy('sort_order')
            ->take(8)
            ->get()
            ->map(fn (Product $product): array => ProductPresenter::card($product));

        $categories = Category::query()
            ->published()
            ->withCount(['products' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'productsCount' => $category->products_count,
            ]);

        return Inertia::render('site/Home', [
            'sliders' => $sliders,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
        ]);
    }
}
