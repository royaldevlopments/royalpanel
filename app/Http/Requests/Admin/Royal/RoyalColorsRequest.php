<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalColorsRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:primary' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            'royal:successText' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:successBorder' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:successBackground' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            'royal:dangerText' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:dangerBorder' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:dangerBackground' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            'royal:secondaryText' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:secondaryBorder' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:secondaryBackground' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            'royal:gray50' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray100' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray200' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray300' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray400' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray500' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray600' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray700' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray800' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:gray900' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],


            'royal:lightmode_primary' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            'royal:lightmode_successText' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_successBorder' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_successBackground' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            'royal:lightmode_dangerText' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_dangerBorder' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_dangerBackground' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            'royal:lightmode_secondaryText' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_secondaryBorder' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_secondaryBackground' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],

            'royal:lightmode_gray50' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray100' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray200' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray300' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray400' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray500' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray600' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray700' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray800' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'royal:lightmode_gray900' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
        ];
    }
}