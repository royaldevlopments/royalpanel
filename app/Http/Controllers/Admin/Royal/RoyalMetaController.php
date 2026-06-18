<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalMetaRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalMetaController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.meta', [
            'meta_color' => $this->settings->get('settings::royal:meta_color', '#4a35cf'),
            'meta_title' => $this->settings->get('settings::royal:meta_title', 'Royal Panel'),
            'meta_description' => $this->settings->get('settings::royal:meta_description', 'Royal Panel'),
            'meta_image' => $this->settings->get('settings::royal:meta_image', '/royal/meta-tags.png'),
            'meta_favicon' => $this->settings->get('settings::royal:meta_favicon', '/royal/Royal.png'),
        ]);
    }

    public function store(RoyalMetaRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.meta');
    }
}