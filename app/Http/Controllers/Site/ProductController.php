<?php

namespace App\Http\Controllers\Site;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\ProductPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $categorySlug = (string) $request->string('categoria');
        $search = (string) $request->string('busca');

        $products = Inertia::scroll(fn () => Product::query()
            ->published()
            ->with(['media', 'category'])
            ->when($categorySlug !== '', fn ($query) => $query
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug)))
            ->when($search !== '', fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(reference) LIKE ?', ['%'.mb_strtolower($search).'%'])))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $product): array => ProductPresenter::card($product)));

        $categories = Category::query()
            ->published()
            ->whereRelation('products', 'status', PublishStatus::Published)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Category $category): array => [
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

        return Inertia::render('site/products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'categoria' => $categorySlug !== '' ? $categorySlug : null,
                'busca' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function show(Product $product): Response
    {
        abort_unless($product->status === PublishStatus::Published, 404);

        $product->load(['media', 'category']);

        return Inertia::render('site/products/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'reference' => $product->reference,
                'description' => $product->description,
                'widthCm' => $product->width_cm,
                'heightCm' => $product->height_cm,
                'weightKg' => $product->weight_kg,
                'category' => $product->category ? [
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
                'images' => $product->media->map(fn ($media): array => [
                    'id' => $media->id,
                    'url' => $media->url(),
                ])->values(),
            ],
            'relatedProducts' => Inertia::defer(fn () => Product::query()
                ->published()
                ->with(['media', 'category'])
                ->whereKeyNot($product->id)
                ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
                ->orderBy('sort_order')
                ->take(4)
                ->get()
                ->map(fn (Product $related): array => ProductPresenter::card($related))),
        ]);
    }
}
