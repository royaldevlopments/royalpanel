<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalAdvancedRequest;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalPresetRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;
use RoyalPanel\Models\Setting;
use RoyalPanel\Traits\Helpers\AvailableLanguages;

class RoyalAdvancedController extends Controller
{
    use AvailableLanguages;

    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        $royalSettings = Setting::query()
            ->where('key', 'like', 'settings::royal:%')
            ->get()
            ->mapWithKeys(function (Setting $setting) {
                $shortKey = preg_replace('/^settings::royal:/', '', $setting->key);
                return [$shortKey => $setting->value];
            })
            ->toArray();

        return $this->view->make('admin.royal.advanced', [
            'profileType' => $this->settings->get('settings::royal:profileType', 'gravatar'),
            'modeToggler' => $this->settings->get('settings::royal:modeToggler', true),
            'langSwitch' => $this->settings->get('settings::royal:langSwitch', true),
            'defaultLang' => $this->settings->get('settings::royal:defaultLang', 'en'),
            'languageOptions' => $this->settings->get('settings::royal:languageOptions', '[{"key":"en","name":"English"}]'),
            'ipFlag' => $this->settings->get('settings::royal:ipFlag', true),
            'lowResourcesAlert' => $this->settings->get('settings::royal:lowResourcesAlert', false),
            'alertLink' => $this->settings->get('settings::royal:alertLink', ''),
            'dashboardPage' => $this->settings->get('settings::royal:dashboardPage', true),
            'registration' => $this->settings->get('settings::royal:registration', false),
            'defaultMode' => $this->settings->get('settings::royal:defaultMode', 'darkmode'),
            'searchComponent' => $this->settings->get('settings::royal:searchComponent', 1),
            'copyright' => $this->settings->get('settings::royal:copyright', 'Designed by Weijers.one'),
            'botToken' => $this->settings->get('settings::royal:botToken', ''),
            'discordBotToken' => $this->settings->get('settings::royal:discordBotToken', ''),
            'discordGuildId' => $this->settings->get('settings::royal:discordGuildId', ''),
            'discordAdminRoleId' => $this->settings->get('settings::royal:discordAdminRoleId', ''),
            'enforceDiscordLink' => $this->settings->get('settings::royal:enforceDiscordLink', false),
            'royalSettings' => $royalSettings,
            'languages' => $this->getAvailableLanguages(false),
        ]);
    }

    public function preset(RoyalPresetRequest $request)
    {
        $validated = $request->validated();
        unset($validated['preset_json']);
        foreach ($validated as $key => $value) {
            $this->settings->set("settings::royal:{$key}", $value);
        }
        $this->alert->success('Preset imported and applied successfully.')->flash();
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'applied' => $validated]);
        }
        return redirect()->route('admin.royal.advanced');
    }

    public function store(RoyalAdvancedRequest $request)
    {
        $data = $request->validated();
        if (isset($data['royal:languageOptions']) && is_array($data['royal:languageOptions'])) {
            $languages = $this->getAvailableLanguages();
            $languageOptions = [];
            foreach ($data['royal:languageOptions'] as $key) {
                if (is_string($key) && isset($languages[$key])) {
                    $languageOptions[] = ['key' => $key, 'name' => $languages[$key]];
                }
            }
            $data['royal:languageOptions'] = json_encode($languageOptions);
        }
        foreach ($data as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.advanced');
    }
}