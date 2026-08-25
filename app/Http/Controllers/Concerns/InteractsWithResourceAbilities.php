<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;

trait InteractsWithResourceAbilities
{
    /**
     * Resolve the abilities the current user has on a resource, used by
     * list pages to hide actions the user cannot perform.
     *
     * @param  class-string<Model>  $modelClass
     * @return array{create: bool, update: bool, delete: bool}
     */
    protected function resolveResourceAbilities(string $modelClass): array
    {
        $user = request()->user();

        return [
            'create' => $user?->can('create', $modelClass) ?? false,
            'update' => $user?->can('update', $modelClass) ?? false,
            'delete' => $user?->can('delete', $modelClass) ?? false,
        ];
    }
}
