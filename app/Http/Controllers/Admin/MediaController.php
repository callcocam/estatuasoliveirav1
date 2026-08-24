<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaStoreRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Map of accepted mediable aliases to their model class.
     *
     * @var array<string, class-string<Model>>
     */
    private const MEDIABLE_TYPES = [
        'product' => Product::class,
        'slider' => Slider::class,
        'category' => Category::class,
    ];

    public function store(MediaStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $modelClass = self::MEDIABLE_TYPES[$data['mediable_type']];
        $model = $modelClass::query()->findOrFail($data['mediable_id']);

        $file = $request->file('file');
        $directory = Str::plural($data['mediable_type']).'/'.$model->getKey();
        $path = $file->store($directory, 'public');

        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

        $model->media()->create([
            'collection' => $data['collection'] ?? 'default',
            'disk' => 'public',
            'path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sort_order' => ((int) $model->media()->max('sort_order')) + 1,
            'custom_properties' => array_filter([
                'width' => $width,
                'height' => $height,
                'alt' => $data['alt'] ?? null,
            ]),
        ]);

        return back();
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $validated = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
        ]);

        $properties = $media->custom_properties ?? [];
        $properties['alt'] = $validated['alt'] ?? null;

        $media->update(['custom_properties' => $properties]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Media::query()->whereKey($id)->update(['sort_order' => $index]);
        }

        return back();
    }

    public function destroy(Media $media): RedirectResponse
    {
        Storage::disk($media->disk)->delete($media->path);

        $media->forceDelete();

        return back();
    }
}
