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
            'royal:logoPosition' => 'required|numeric',
            'royal:socialPosition' => 'required|numeric',
            'royal:loginLayout' => 'required|numeric',
            'royal:loginGradient' => 'required|in:true,false',
            'royal:heroBadge' => 'string|max:255',
            'royal:heroTitle' => 'string|max:255',
            'royal:heroTagline' => 'string|max:500',
        ];
    }
}