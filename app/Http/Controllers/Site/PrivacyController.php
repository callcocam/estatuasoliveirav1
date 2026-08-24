<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\LegalContent;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('site/Privacy', [
            'content' => Setting::get('content_privacy'),
            'legal' => LegalContent::for('privacy'),
        ]);
    }
}
