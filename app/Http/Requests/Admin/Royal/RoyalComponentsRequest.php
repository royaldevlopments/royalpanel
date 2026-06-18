<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalComponentsRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:serverRow' => 'required|numeric',

            'royal:statsCards' => 'required|numeric',
            'royal:sideGraphs' => 'required|numeric',
            'royal:graphs' => 'required|numeric',
        ];
    }
}