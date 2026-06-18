<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalSocialRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalSocialController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.social', [
            'socials' => json_decode($this->settings->get('settings::royal:socials', '[]'), true),
            'socialButtons' => $this->settings->get('settings::royal:socialButtons', false),
            'discordBox' => $this->settings->get('settings::royal:discordBox', true),
        ]);
    }

    public function store(RoyalSocialRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            if (is_array($value)) {
                $value = array_filter($value, fn($item) => $item !== '' && $item !== null);
                if (empty($value)) {
                    continue;
                }
                $value = json_encode($value);
            } else {
                if ($value === '' || $value === null) {
                    continue;
                }
            }
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.social');
    }
}