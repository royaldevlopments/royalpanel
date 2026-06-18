<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:logo' => ['required', 'string', 'regex:/^(https?:\/\/[^\s]+|\/[^\s]*)$/i'],
            'royal:logoLight' => 'required|string',
            'royal:fullLogo' => 'required|in:true,false',
            'royal:logoHeight' => 'required|numeric',
            'royal:discord' => 'nullable|numeric',
            'royal:support' => 'nullable|string|url',
        ];
    }
}