<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalDashboardRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:dashboardWidgets' => 'required|array',
        ];
    }
}