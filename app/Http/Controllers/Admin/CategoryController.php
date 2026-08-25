<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithResourceAbilities;
use App\Http\Controllers\Concerns\InteractsWithTrashedFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Models\Category;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use InteractsWithResourceAbilities;
    use InteractsWithTrashedFilter;

    /**
     * The list is intentionally not paginated: manual reordering needs the
     * whole ordered collection on the client.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Category::class);

        $search = (string) $request->string('search');
        $status = (string) $request->string('status');
        $trashed = $this->resolveTrashedFilter($request);

        return Inertia::render('admin/categories/Index', [
            'categories' => $this->applyTrashedToQuery(Category::query(), $trashed)
                ->when($status !== '', fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query
                    ->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%']))
                ->withCount('products')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'status' => $category->status->value,
                    'sortOrder' => $category->sort_order,
                    'productsCount' => $category->products_count,
                    'deleted' => $category->trashed(),
                ]),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'trashed' => $trashed,
            ],
            'can' => $this->resolveResourceAbilities(Category::class),
        ]);
    }

    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::for(Category::class, $data['slug'] ?: $data['name']);

        Category::query()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.created')]);

        return back();
    }

    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::for(Category::class, $data['slug'] ?: $data['name'], $category->id);

        $category->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.updated')]);

        return back();
    }

    /**
     * Soft delete on the first call; permanently delete when the category is
     * already trashed (products keep existing via `nullOnDelete`).
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->trashed()) {
            $category->forceDelete();

            Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.force_deleted')]);

            return back();
        }

        $category->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.deleted')]);

        return back();
    }

    public function restore(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.restored')]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->authorize('update', Category::class);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Category::query()->withTrashed()->whereKey($id)->update(['sort_order' => $index]);
        }

        return back();
    }
}
