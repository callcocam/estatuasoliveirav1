<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Database\Eloquent\Model;

trait ResolvesRouteModelId
{
    /**
     * Get the primary key of a bound route model, if present.
     */
    protected function routeModelId(string $parameter): int|string|null
    {
        $model = $this->route($parameter);

        return $model instanceof Model ? $model->getKey() : null;
    }
}
