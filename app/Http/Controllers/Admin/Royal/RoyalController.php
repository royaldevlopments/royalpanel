<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.index', [
            'logo' => $this->settings->get('settings::royal:logo', '/royal/Royal.png'),
            'logoLight' => $this->settings->get('settings::royal:logoLight', '/royal/Royal.png'),
            'fullLogo' => $this->settings->get('settings::royal:fullLogo', 'false'),
            'logoHeight' => $this->settings->get('settings::royal:logoHeight', '32'),
            'discord' => $this->settings->get('settings::royal:discord', '715281172422197300'),
            'support' => $this->settings->get('settings::royal:support', 'https://discord.gg/geCjrRbAwC'),
        ]);
    }

    public function store(RoyalRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal');
    }
}