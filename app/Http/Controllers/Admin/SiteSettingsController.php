<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SiteTheme;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
{
    /**
     * Map of editable setting keys to their group.
     *
     * @var array<string, string>
     */
    private const KEY_GROUPS = [
        'company_name' => 'company',
        'company_document' => 'company',
        'company_about' => 'company',
        'contact_phone' => 'contact',
        'contact_whatsapp' => 'contact',
        'contact_email' => 'contact',
        'address_street' => 'address',
        'address_number' => 'address',
        'address_complement' => 'address',
        'address_district' => 'address',
        'address_city' => 'address',
        'address_state' => 'address',
        'address_zip' => 'address',
        'content_terms' => 'content',
        SiteTheme::SETTING_KEY => 'theme',
    ];

    public function edit(): Response
    {
        $values = [];

        foreach (array_keys(self::KEY_GROUPS) as $key) {
            $values[$key] = Setting::get($key);
        }

        $values[SiteTheme::SETTING_KEY] ??= SiteTheme::Stone->value;

        $logoPath = Setting::get('branding_logo_path');

        return Inertia::render('admin/settings/Site', [
            'values' => $values,
            'logoUrl' => $logoPath ? Storage::disk('public')->url($logoPath) : null,
            'themes' => array_map(fn (SiteTheme $theme): array => [
                'value' => $theme->value,
                'label' => $theme->label(),
            ], SiteTheme::cases()),
        ]);
    }

    public function update(SiteSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        foreach (self::KEY_GROUPS as $key => $group) {
            Setting::set($key, $data[$key] ?? null, $group);
        }

        if ($request->hasFile('logo')) {
            $previous = Setting::get('branding_logo_path');
            $path = $request->file('logo')->store('branding', 'public');

            if (is_string($path)) {
                Setting::set('branding_logo_path', $path, 'branding');

                if (is_string($previous) && $previous !== $path) {
                    Storage::disk('public')->delete($previous);
                }
            }
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('app.admin.settings.saved')]);

        return back();
    }
}
