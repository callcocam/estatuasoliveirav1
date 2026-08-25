<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

trait InteractsWithDeferredIndex
{
    /**
     * Resolve a whitelisted page size for index listings.
     */
    protected function resolvePerPage(Request $request, int $default = 15): int
    {
        $perPage = $request->integer('per_page');

        return in_array($perPage, [10, 15, 25, 50], true) ? $perPage : $default;
    }

    /**
     * Render an index page deferring only the paginator prop.
     *
     * @param  callable(): LengthAwarePaginator  $paginator
     * @param  array<string, mixed>  $props
     */
    protected function renderDeferredIndex(string $component, string $propName, callable $paginator, array $props = []): Response
    {
        return Inertia::render($component, [
            ...$props,
            $propName => Inertia::defer($paginator),
        ]);
    }
}
