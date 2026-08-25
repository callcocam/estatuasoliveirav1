<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait InteractsWithTrashedFilter
{
    /**
     * Resolve the trashed filter from the request (`without`, `only` or `with`).
     */
    protected function resolveTrashedFilter(Request $request): string
    {
        $trashed = (string) $request->string('trashed');

        return in_array($trashed, ['only', 'with'], true) ? $trashed : 'without';
    }

    /**
     * Apply the trashed filter to a soft-deletable query.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    protected function applyTrashedToQuery(Builder $query, string $trashed): Builder
    {
        return match ($trashed) {
            'only' => $query->onlyTrashed(),
            'with' => $query->withTrashed(),
            default => $query,
        };
    }
}
