<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $status = (string) $request->string('status');
        $categoryId = (string) $request->string('category');
        $search = (string) $request->string('search');

        $products = Product::query()
            ->withTrashed()
            ->with(['media', 'category'])
            ->when($status === 'trashed', fn ($query) => $query->whereNotNull('deleted_at'))
            ->when($status !== '' && $status !== 'trashed', fn ($query) => $query
                ->whereNull('deleted_at')
                ->where('status', $status))
            ->when($status === '', fn ($query) => $query->whereNull('deleted_at'))
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when($search !== '', fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(reference) LIKE ?', ['%'.mb_strtolower($search).'%'])))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'reference' => $product->reference,
                'categoryName' => $product->category?->name,
                'status' => $product->status->value,
                'statusLabel' => $product->status->label(),
                'featured' => $product->featured,
                'stock' => $product->stock,
                'image' => $product->coverMedia()?->url(),
                'deleted' => $product->trashed(),
            ]);

        return Inertia::render('admin/products/Index', [
            'products' => $products,
            'categories' => $this->categoryOptions(),
            'filters' => [
                'status' => $status !== '' ? $status : null,
                'category' => $categoryId !== '' ? $categoryId : null,
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/Form', [
            'product' => null,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::for(Product::class, $data['slug'] ?: $data['name']);

        $product = Product::query()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.created')]);

        return to_route('admin.products.edit', $product);
    }

    public function edit(Product $product): Response
    {
        $product->load('media');

        return Inertia::render('admin/products/Form', [
            'product' => [
                'id' => $product->id,
                'categoryId' => $product->category_id,
                'name' => $product->name,
                'slug' => $product->slug,
                'reference' => $product->reference,
                'description' => $product->description,
                'status' => $product->status->value,
                'featured' => $product->featured,
                'price' => $product->price,
                'widthCm' => $product->width_cm,
                'heightCm' => $product->height_cm,
                'weightKg' => $product->weight_kg,
                'stock' => $product->stock,
                'sortOrder' => $product->sort_order,
                'media' => $product->media->map(fn (Media $media): array => [
                    'id' => $media->id,
                    'url' => $media->url(),
                    'alt' => $media->custom_properties['alt'] ?? null,
                ])->values(),
            ],
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::for(Product::class, $data['slug'] ?: $data['name'], $product->id);

        $product->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.updated')]);

        return back();
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.deleted')]);

        return to_route('admin.products.index');
    }

    public function restore(Product $product): RedirectResponse
    {
        $product->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.restored')]);

        return back();
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $copy = $product->replicate(['slug']);
        $copy->name = __('app.admin.products.copy_name', ['name' => $product->name]);
        $copy->slug = UniqueSlug::for(Product::class, $copy->name);
        $copy->status = PublishStatus::Draft;
        $copy->save();

        foreach ($product->media as $media) {
            $extension = pathinfo($media->path, PATHINFO_EXTENSION);
            $newPath = "products/{$copy->id}/".str()->uuid().($extension !== '' ? ".{$extension}" : '');

            if (! Storage::disk($media->disk)->copy($media->path, $newPath)) {
                continue;
            }

            $copy->media()->create([
                'collection' => $media->collection,
                'disk' => $media->disk,
                'path' => $newPath,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'sort_order' => $media->sort_order,
                'custom_properties' => $media->custom_properties,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.duplicated')]);

        return to_route('admin.products.edit', $copy);
    }

    /**
     * @return Collection<int, array{id: string, name: string}>
     */
    private function categoryOptions(): Collection
    {
        return Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ]);
    }
}
