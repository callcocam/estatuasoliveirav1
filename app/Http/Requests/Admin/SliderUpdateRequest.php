<?php

namespace App\Http\Requests\Admin;

class SliderUpdateRequest extends SliderStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('slider')) ?? false;
    }
}
