<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalStylingRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalStylingController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.styling', [
            'pageTitle' => $this->settings->get('settings::royal:pageTitle', 'true'),
            'background' => $this->settings->get('settings::royal:background', 'true'),
            'backgroundImage' => $this->settings->get('settings::royal:backgroundImage', ''),
            'backgroundImageLight' => $this->settings->get('settings::royal:backgroundImageLight', ''),
            'loginBackground' => $this->settings->get('settings::royal:loginBackground', '/royal/background-login.png'),
            'backgroundFaded' => $this->settings->get('settings::royal:backgroundFaded', 'default'),
            'backdrop' => $this->settings->get('settings::royal:backdrop', 'false'),
            'backdropPercentage' => $this->settings->get('settings::royal:backdropPercentage', '100'),
            'radiusInput' => $this->settings->get('settings::royal:radiusInput', '7'),
            'radiusBox' => $this->settings->get('settings::royal:radiusBox', '10'),
            'borderInput' => $this->settings->get('settings::royal:borderInput', 'true'),
            'flashMessage' => $this->settings->get('settings::royal:flashMessage', '1'),
            'font' => $this->settings->get('settings::royal:font', 'default'),
            'icon' => $this->settings->get('settings::royal:icon', 'heroicons'),
        ]);
    }

    public function store(RoyalStylingRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.styling');
    }
}