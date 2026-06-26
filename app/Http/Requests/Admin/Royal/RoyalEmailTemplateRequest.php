<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use Illuminate\Foundation\Http\FormRequest;

class RoyalEmailTemplateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subject' => 'nullable|string|max:255',
            'greeting' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'action_text' => 'nullable|string|max:255',
            'action_url' => 'nullable|string|max:512',
            'level' => 'nullable|in:primary,error',
            'outro' => 'nullable|string',
            'enabled' => 'nullable|in:true,false,1,0,on,off',
        ];
    }
}
