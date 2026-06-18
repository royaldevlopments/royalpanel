<?php

namespace RoyalPanel\Http\Requests\Admin\Nest;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class StoreNestFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:191|regex:/^[\w\- ]+$/',
            'description' => 'string|nullable',
        ];
    }
}
