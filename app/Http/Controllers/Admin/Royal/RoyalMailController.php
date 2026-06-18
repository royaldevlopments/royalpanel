<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalMailRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalMailController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.mail', [
            'mail_color' => $this->settings->get('settings::royal:mail_color', '#4a35cf'),
            'mail_backgroundColor' => $this->settings->get('settings::royal:mail_backgroundColor', '#F5F5FF'),
            'mail_logo' => $this->settings->get('settings::royal:mail_logo', 'https://royal.gg/royal.png'),
            'mail_logoFull' => $this->settings->get('settings::royal:mail_logoFull', false),
            'mail_mode' => $this->settings->get('settings::royal:mail_mode', 'light'),
            'mail_discord' => $this->settings->get('settings::royal:mail_discord', 'https://royal.gg/discord'),
            'mail_twitter' => $this->settings->get('settings::royal:mail_twitter', 'https://x.com'),
            'mail_facebook' => $this->settings->get('settings::royal:mail_facebook', 'https://facebook.com'),
            'mail_instagram' => $this->settings->get('settings::royal:mail_instagram', 'https://instagram.com'),
            'mail_linkedin' => $this->settings->get('settings::royal:mail_linkedin', 'https://linkedin.com'),
            'mail_youtube' => $this->settings->get('settings::royal:mail_youtube', 'https://youtube.com'),
            'mail_status' => $this->settings->get('settings::royal:mail_status', 'https://royal.gg/status'),
            'mail_billing' => $this->settings->get('settings::royal:mail_billing', 'https://royal.gg/billing'),
            'mail_support' => $this->settings->get('settings::royal:mail_support', 'https://royal.gg/support'),
        ]);
    }

    public function store(RoyalMailRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.mail');
    }
}