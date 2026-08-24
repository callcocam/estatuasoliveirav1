<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class TermsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('site/Terms', [
            'content' => Setting::get('content_terms'),
        ]);
    }
}
