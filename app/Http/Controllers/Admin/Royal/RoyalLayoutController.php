<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalLayoutRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalLayoutController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.layout', [
            'layout' => $this->settings->get('settings::royal:layout', 1),
            'searchComponent' => $this->settings->get('settings::royal:searchComponent', 1),
            'logoPosition' => $this->settings->get('settings::royal:logoPosition', 1),
            'socialPosition' => $this->settings->get('settings::royal:socialPosition', 1),
            'loginLayout' => $this->settings->get('settings::royal:loginLayout', 1),
        ]);
    }

    public function store(RoyalLayoutRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.layout');
    }
}