<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactStoreRequest;
use App\Mail\ContactMessageConfirmation;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('site/Contact', [
            'prefill' => $this->productPrefill((string) $request->string('produto')),
        ]);
    }

    /**
     * @return array{subject: string, message: string}|null
     */
    private function productPrefill(string $productSlug): ?array
    {
        if ($productSlug === '') {
            return null;
        }

        $product = Product::query()->published()->where('slug', $productSlug)->first();

        if (! $product) {
            return null;
        }

        $productLabel = $product->reference !== null && $product->reference !== ''
            ? __('app.site.contact.product_label', ['name' => $product->name, 'reference' => $product->reference])
            : $product->name;

        return [
            'subject' => __('app.site.contact.product_subject', ['product' => $productLabel]),
            'message' => __('app.site.contact.product_message', [
                'product' => $productLabel,
                'url' => route('products.show', $product),
            ]),
        ];
    }

    public function store(ContactStoreRequest $request): RedirectResponse
    {
        if (! $request->isSpam()) {
            $contactMessage = ContactMessage::query()->create($request->safe()->except('website'));

            if ($recipient = Setting::get('contact_email')) {
                Mail::to($recipient)->queue(new ContactMessageReceived($contactMessage));
            }

            Mail::to($contactMessage->email)->queue(new ContactMessageConfirmation($contactMessage));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.site.contact.success')]);

        return back();
    }
}
