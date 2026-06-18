<?php

namespace RoyalPanel\Http\Requests\Admin\Settings;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class AdvancedSettingsFormRequest extends AdminFormRequest
{
    /**
     * Return all the rules to apply to this request's data.
     */
    public function rules(): array
    {
        return [
            'recaptcha:enabled' => 'required|in:true,false',
            'recaptcha:method' => 'nullable|in:turnstile,recaptcha',
            'recaptcha:secret_key' => 'required|string|max:191',
            'recaptcha:website_key' => 'required|string|max:191',
            'turnstile:site_key' => 'nullable|string|max:191',
            'turnstile:site_secret' => 'nullable|string|max:191',
            'royalpanel:guzzle:timeout' => 'required|integer|between:1,60',
            'royalpanel:guzzle:connect_timeout' => 'required|integer|between:1,60',
            'royalpanel:client_features:allocations:enabled' => 'required|in:true,false',
            'royalpanel:client_features:allocations:range_start' => [
                'nullable',
                'required_if:royalpanel:client_features:allocations:enabled,true',
                'integer',
                'between:1024,65535',
            ],
            'royalpanel:client_features:allocations:range_end' => [
                'nullable',
                'required_if:royalpanel:client_features:allocations:enabled,true',
                'integer',
                'between:1024,65535',
                'gt:royalpanel:client_features:allocations:range_start',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'recaptcha:enabled' => 'reCAPTCHA Enabled',
            'recaptcha:secret_key' => 'reCAPTCHA Secret Key',
            'recaptcha:website_key' => 'reCAPTCHA Website Key',
            'turnstile:site_key' => 'Cloudflare Turnstile Site Key',
            'turnstile:site_secret' => 'Cloudflare Turnstile Secret Key',
            'royalpanel:guzzle:timeout' => 'HTTP Request Timeout',
            'royalpanel:guzzle:connect_timeout' => 'HTTP Connection Timeout',
            'royalpanel:client_features:allocations:enabled' => 'Auto Create Allocations Enabled',
            'royalpanel:client_features:allocations:range_start' => 'Starting Port',
            'royalpanel:client_features:allocations:range_end' => 'Ending Port',
        ];
    }
}
