<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalMailRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:mail_color' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:mail_backgroundColor' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:mail_logo' => 'required|string',
            'royal:mail_logoFull' => 'required|in:true,false',
            'royal:mail_mode' => 'required|string',

            'royal:mail_discord' => 'nullable|url',
            'royal:mail_twitter' => 'nullable|string|url',
            'royal:mail_facebook' => 'nullable|string|url',
            'royal:mail_instagram' => 'nullable|string|url',
            'royal:mail_linkedin' => 'nullable|string|url',
            'royal:mail_youtube' => 'nullable|string|url',

            'royal:mail_status' => 'nullable|string|url',
            'royal:mail_billing' => 'nullable|string|url',
            'royal:mail_support' => 'nullable|string|url',
        ];
    }
}