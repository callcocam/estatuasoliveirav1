<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublishStatus;
use App\Http\Controllers\Concerns\InteractsWithDeferredIndex;
use App\Http\Controllers\Concerns\InteractsWithResourceAbilities;
use App\Http\Controllers\Concerns\InteractsWithTrashedFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateProductDescriptionRequest;
use App\Http\Requests\Admin\ProductStoreRequest;
use App\Http\Requests\Admin\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Services\Ai\ProductDescriptionGenerator;
use App\Services\Ai\TextGenerationFailedException;
use App\Support\UniqueSlug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use InteractsWithDeferredIndex;
    use InteractsWithResourceAbilities;
    use InteractsWithTrashedFilter;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        return $this->renderDeferredIndex(
            'admin/products/Index',
            'products',
            fn (): LengthAwarePaginator => $this->productsPaginator($request),
            [
                'categories' => $this->categoryOptions(),
                'filters' => [
                    'search' => (string) $request->string('search'),
                    'status' => (string) $request->string('status'),
                    'category' => (string) $request->string('category'),
                    'trashed' => $this->resolveTrashedFilter($request),
                    'per_page' => (string) $this->resolvePerPage($request),
                ],
                'can' => $this->resolveResourceAbilities(Product::class),
            ],
        );
    }

    public function create(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('admin/products/Form', [
            'product' => null,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::for(Product::class, $data['slug'] ?: $data['name']);

        $product = Product::query()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.created')]);

        return to_route('admin.products.edit', $product);
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

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

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::for(Product::class, $data['slug'] ?: $data['name'], $product->id);

        $product->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.updated')]);

        return back();
    }

    public function generateDescription(
        GenerateProductDescriptionRequest $request,
        ProductDescriptionGenerator $generator,
    ): JsonResponse {
        $this->authorize('create', Product::class);

        try {
            $description = $generator->generate($request->validated());
        } catch (TextGenerationFailedException) {
            throw ValidationException::withMessages([
                'description' => __('app.admin.products.ai.failed'),
            ]);
        }

        return response()->json(['description' => $description]);
    }

    /**
     * Soft delete on the first call; permanently delete (with media files)
     * when the product is already trashed.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        if ($product->trashed()) {
            foreach ($product->media as $media) {
                Storage::disk($media->disk)->delete($media->path);
                $media->forceDelete();
            }

            $product->forceDelete();

            Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.force_deleted')]);

            return to_route('admin.products.index', ['trashed' => 'only']);
        }

        $product->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.deleted')]);

        return to_route('admin.products.index');
    }

    public function restore(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.products.restored')]);

        return back();
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $this->authorize('create', Product::class);

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
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private function productsPaginator(Request $request): LengthAwarePaginator
    {
        $status = (string) $request->string('status');
        $categoryId = (string) $request->string('category');
        $search = (string) $request->string('search');
        $trashed = $this->resolveTrashedFilter($request);

        return $this->applyTrashedToQuery(Product::query(), $trashed)
            ->with(['media', 'category'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when($search !== '', fn ($query) => $query
                ->where(fn ($searchQuery) => $searchQuery
                    ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(reference) LIKE ?', ['%'.mb_strtolower($search).'%'])))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($this->resolvePerPage($request))
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
