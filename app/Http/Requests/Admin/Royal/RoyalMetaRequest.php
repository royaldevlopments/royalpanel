<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalMetaRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:meta_color' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:meta_title' => 'required|string',
            'royal:meta_description' => 'required|string',
            'royal:meta_image' => ['required', 'string', 'regex:/^(https?:\/\/[^\s]+|\/[^\s]*)$/i'],
            'royal:meta_favicon' => ['required', 'string', 'regex:/^(https?:\/\/[^\s]+|\/[^\s]*)$/i'],
        ];
    }
}