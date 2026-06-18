<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalStylingRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:pageTitle' => 'required|in:true,false',

            'royal:background' => 'required|in:true,false',
            'royal:backgroundImage' => 'nullable|string',
            'royal:backgroundImageLight' => 'nullable|string',
            'royal:loginBackground' => 'nullable|string',
            'royal:backgroundFaded' => 'nullable|string',

            'royal:backdrop' => 'required|in:true,false',
            'royal:backdropPercentage' => 'required|numeric',
            
            'royal:radiusInput' => 'required|numeric',
            'royal:borderInput' => 'required|in:true,false',
            'royal:radiusBox' => 'required|numeric',

            'royal:flashMessage' => 'required|numeric',

            'royal:font' => 'required|string',
            'royal:icon' => 'required|string',
        ];
    }
}