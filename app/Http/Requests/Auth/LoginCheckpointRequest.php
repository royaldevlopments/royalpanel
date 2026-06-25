<?php

namespace RoyalPanel\Http\Requests\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class LoginCheckpointRequest extends FormRequest
{
    /**
     * Determine if the request is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules to apply to the request.
     */
    public function rules(): array
    {
        return [
            'confirmation_token' => 'required|string',
            'authentication_code' => [
                'nullable',
                'numeric',
                Rule::requiredIf(function () {
                    return empty($this->input('recovery_token')) && empty($this->input('discord_2fa_code'));
                }),
            ],
            'recovery_token' => [
                'nullable',
                'string',
                Rule::requiredIf(function () {
                    return empty($this->input('authentication_code')) && empty($this->input('discord_2fa_code'));
                }),
            ],
            'discord_2fa_code' => [
                'nullable',
                'string',
                'size:6',
                Rule::requiredIf(function () {
                    return empty($this->input('authentication_code')) && empty($this->input('recovery_token'));
                }),
            ],
        ];
    }
}
