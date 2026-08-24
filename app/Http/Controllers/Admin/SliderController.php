<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SliderRequest;
use App\Models\Slider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SliderController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = (string) $request->string('filter');
        $search = (string) $request->string('search');

        return Inertia::render('admin/sliders/Index', [
            'sliders' => Slider::query()
                ->withTrashed()
                ->when($filter === 'trashed', fn ($query) => $query->whereNotNull('deleted_at'))
                ->when($filter !== 'trashed' && $filter !== '', fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where('status', $filter))
                ->when($filter === '', fn ($query) => $query->whereNull('deleted_at'))
                ->when($search !== '', fn ($query) => $query
                    ->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($search).'%']))
                ->with('media')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Slider $slider): array => [
                    'id' => $slider->id,
                    'title' => $slider->title,
                    'subtitle' => $slider->subtitle,
                    'status' => $slider->status->value,
                    'sortOrder' => $slider->sort_order,
                    'image' => $slider->coverMedia()?->url(),
                    'deleted' => $slider->trashed(),
                ]),
            'filters' => [
                'filter' => $filter !== '' ? $filter : null,
                'search' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/sliders/Form', ['slider' => null]);
    }

    public function store(SliderRequest $request): RedirectResponse
    {
        $slider = Slider::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.created')]);

        return to_route('admin.sliders.edit', $slider);
    }

    public function edit(Slider $slider): Response
    {
        $slider->load('media');

        return Inertia::render('admin/sliders/Form', [
            'slider' => [
                'id' => $slider->id,
                'title' => $slider->title,
                'subtitle' => $slider->subtitle,
                'description' => $slider->description,
                'ctaLabel' => $slider->cta_label,
                'ctaUrl' => $slider->cta_url,
                'status' => $slider->status->value,
                'sortOrder' => $slider->sort_order,
                'media' => $slider->media->map(fn ($media): array => [
                    'id' => $media->id,
                    'url' => $media->url(),
                    'alt' => $media->custom_properties['alt'] ?? null,
                ])->values(),
            ],
        ]);
    }

    public function update(SliderRequest $request, Slider $slider): RedirectResponse
    {
        $slider->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.updated')]);

        return back();
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        $slider->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.deleted')]);

        return to_route('admin.sliders.index');
    }

    public function restore(Slider $slider): RedirectResponse
    {
        $slider->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.restored')]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Slider::query()->whereKey($id)->update(['sort_order' => $index]);
        }

        return back();
    }
}
