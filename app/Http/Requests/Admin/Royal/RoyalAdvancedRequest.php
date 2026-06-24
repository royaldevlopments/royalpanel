<?php

namespace RoyalPanel\Http\Requests\Admin\Royal;

use RoyalPanel\Traits\Helpers\AvailableLanguages;
use RoyalPanel\Http\Requests\Admin\AdminFormRequest;

class RoyalAdvancedRequest extends AdminFormRequest
{
    use AvailableLanguages;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'royal:profileType' => 'required|string',
            'royal:modeToggler' => 'required|in:true,false',
            'royal:langSwitch' => 'required|in:true,false',
            'royal:defaultLang' => 'required|string|in:' . implode(',', array_keys($this->getAvailableLanguages())),
            'royal:languageOptions' => 'required|array|min:1',
            'royal:ipFlag' => 'required|in:true,false',
            'royal:lowResourcesAlert' => 'required|in:true,false',
            'royal:alertLink' => 'nullable|url|max:255',
            'royal:dashboardPage' => 'required|in:true,false',
            'royal:registration' => 'required|in:true,false',
            'royal:defaultMode' => 'required|in:darkmode,lightmode',
            'royal:searchComponent' => 'required|numeric',
            'royal:copyright' => 'required|string|max:255',
            'royal:botToken' => 'nullable|string|max:255',
            'royal:discordBotToken' => 'nullable|string|max:255',
            'royal:discordGuildId' => 'nullable|string|max:255',
            'royal:discordAdminRoleId' => 'nullable|string|max:255',
            'royal:enforceDiscordLink' => 'nullable|in:true,false',
        ];
    }
}