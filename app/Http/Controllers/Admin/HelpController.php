<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HelpController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/Help', [
            'intro' => __('app.admin.help.intro'),
            'sections' => __('app.admin.help.sections'),
        ]);
    }
}
