<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalLayoutRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:layout' => 'required|numeric',
            'royal:searchComponent' => 'required|numeric',

            'royal:logoPosition' => 'required|numeric',
            'royal:socialPosition' => 'required|numeric',
            'royal:loginLayout' => 'required|numeric',
        ];
    }
}