<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalComponentsRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalComponentsController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.components', [
            'serverRow' => $this->settings->get('settings::royal:serverRow', 1),
            'statsCards' => $this->settings->get('settings::royal:statsCards', 2),
            'sideGraphs' => $this->settings->get('settings::royal:sideGraphs', 2),
            'graphs' => $this->settings->get('settings::royal:graphs', 2),
        ]);
    }

    public function store(RoyalComponentsRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.components');
    }
}