<?php

namespace RoyalPanel\Http\Requests\Admin\Settings;

use Illuminate\Validation\Rule;
use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class BaseSettingsFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'app:name' => 'required|string|max:191',
            'royalpanel:auth:2fa_required' => 'required|integer|in:0,1,2',
        ];
    }

    public function attributes(): array
    {
        return [
            'app:name' => 'Company Name',
            'royalpanel:auth:2fa_required' => 'Require 2-Factor Authentication',
        ];
    }
}
