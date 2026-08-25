<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\InteractsWithResourceAbilities;
use App\Http\Controllers\Concerns\InteractsWithTrashedFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SliderStoreRequest;
use App\Http\Requests\Admin\SliderUpdateRequest;
use App\Models\Slider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SliderController extends Controller
{
    use InteractsWithResourceAbilities;
    use InteractsWithTrashedFilter;

    /**
     * The list is intentionally not paginated: manual reordering needs the
     * whole ordered collection on the client.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Slider::class);

        $search = (string) $request->string('search');
        $status = (string) $request->string('status');
        $trashed = $this->resolveTrashedFilter($request);

        return Inertia::render('admin/sliders/Index', [
            'sliders' => $this->applyTrashedToQuery(Slider::query(), $trashed)
                ->when($status !== '', fn ($query) => $query->where('status', $status))
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
                'search' => $search,
                'status' => $status,
                'trashed' => $trashed,
            ],
            'can' => $this->resolveResourceAbilities(Slider::class),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Slider::class);

        return Inertia::render('admin/sliders/Form', ['slider' => null]);
    }

    public function store(SliderStoreRequest $request): RedirectResponse
    {
        $slider = Slider::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.created')]);

        return to_route('admin.sliders.edit', $slider);
    }

    public function edit(Slider $slider): Response
    {
        $this->authorize('update', $slider);

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

    public function update(SliderUpdateRequest $request, Slider $slider): RedirectResponse
    {
        $slider->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.updated')]);

        return back();
    }

    /**
     * Soft delete on the first call; permanently delete (with media files)
     * when the slider is already trashed.
     */
    public function destroy(Slider $slider): RedirectResponse
    {
        $this->authorize('delete', $slider);

        if ($slider->trashed()) {
            foreach ($slider->media as $media) {
                Storage::disk($media->disk)->delete($media->path);
                $media->forceDelete();
            }

            $slider->forceDelete();

            Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.force_deleted')]);

            return to_route('admin.sliders.index', ['trashed' => 'only']);
        }

        $slider->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.deleted')]);

        return to_route('admin.sliders.index');
    }

    public function restore(Slider $slider): RedirectResponse
    {
        $this->authorize('delete', $slider);

        $slider->restore();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.sliders.restored')]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->authorize('update', Slider::class);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Slider::query()->withTrashed()->whereKey($id)->update(['sort_order' => $index]);
        }

        return back();
    }
}
