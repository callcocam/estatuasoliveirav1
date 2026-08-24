<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\UniqueSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->string('search');
        $status = (string) $request->string('status');

        return Inertia::render('admin/categories/Index', [
            'categories' => Category::query()
                ->withTrashed()
                ->when($status === 'trashed', fn ($query) => $query->whereNotNull('deleted_at'))
                ->when($status !== '' && $status !== 'trashed', fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where('status', $status))
                ->when($status === '', fn ($query) => $query->whereNull('deleted_at'))
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
                'search' => $search !== '' ? $search : null,
                'status' => $status !== '' ? $status : null,
            ],
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::for(Category::class, $data['slug'] ?: $data['name']);

        Category::query()->create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.created')]);

        return back();
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = UniqueSlug::for(Category::class, $data['slug'] ?: $data['name'], $category->id);

        $category->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.updated')]);

        return back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.deleted')]);

        return back();
    }

    public function restore(Category $category): RedirectResponse
    {
        $category->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.categories.restored')]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
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
