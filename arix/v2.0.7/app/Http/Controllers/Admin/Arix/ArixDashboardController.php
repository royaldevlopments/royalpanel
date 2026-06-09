<?php

namespace Pterodactyl\Http\Controllers\Admin\Arix;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\Arix\ArixDashboardRequest;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class ArixDashboardController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.arix.dashboard', [
            'dashboardWidgets' => json_decode($this->settings->get('settings::arix:dashboardWidgets', '[]'), true),
        ]);
    }

    public function store(ArixDashboardRequest $request)
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
        return redirect()->route('admin.arix.dashboard');
    }
}