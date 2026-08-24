<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactStoreRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('site/Contact');
    }

    public function store(ContactStoreRequest $request): RedirectResponse
    {
        if (! $request->isSpam()) {
            $contactMessage = ContactMessage::query()->create($request->safe()->except('website'));

            if ($recipient = Setting::get('contact_email')) {
                Mail::to($recipient)->queue(new ContactMessageReceived($contactMessage));
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.site.contact.success')]);

        return back();
    }
}
