<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalAnnouncementRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalAnnouncementController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.announcement', [
            'announcement' => $this->settings->get('settings::royal:announcement', false),
            'announcementColor' => $this->settings->get('settings::royal:announcementColor', '#16aaaa'),
            'announcementIcon' => $this->settings->get('settings::royal:announcementIcon', 'megaphone'),
            'announcementMessage' => $this->settings->get('settings::royal:announcementMessage', 'We have a brand new game panel design!'),
            'announcementCta' => $this->settings->get('settings::royal:announcementCta', false),
            'announcementCtaTitle' => $this->settings->get('settings::royal:announcementCtaTitle', 'Buy now!'),
            'announcementCtaLink' => $this->settings->get('settings::royal:announcementCtaLink', '/'),
            'announcementDismissable' => $this->settings->get('settings::royal:announcementDismissable', false),
        ]);
    }

    public function store(RoyalAnnouncementRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.announcement');
    }
}