<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalDashboardRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalDashboardController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.dashboard', [
            'dashboardWidgets' => json_decode($this->settings->get('settings::royal:dashboardWidgets', '[]'), true),
        ]);
    }

    public function store(RoyalDashboardRequest $request)
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
        return redirect()->route('admin.royal.dashboard');
    }
}