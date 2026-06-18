<?php

namespace RoyalPanel\Http\Controllers\Admin\Royal;

use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\View\Factory as ViewFactory;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Http\Requests\Admin\Royal\RoyalColorsRequest;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class RoyalColorsController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
        private ViewFactory $view
    ) {}

    public function index(): View
    {
        return $this->view->make('admin.royal.colors', [
            'primary' => $this->settings->get('settings::royal:primary', '#4A35CF'),
            'successText' => $this->settings->get('settings::royal:successText', '#E1FFD8'),
            'successBorder' => $this->settings->get('settings::royal:successBorder', '#56AA2B'),
            'successBackground' => $this->settings->get('settings::royal:successBackground', '#3D8F1F'),
            'dangerText' => $this->settings->get('settings::royal:dangerText', '#FFD8D8'),
            'dangerBorder' => $this->settings->get('settings::royal:dangerBorder', '#AA2A2A'),
            'dangerBackground' => $this->settings->get('settings::royal:dangerBackground', '#8F1F20'),
            'secondaryText' => $this->settings->get('settings::royal:secondaryText', '#B2B2C1'),
            'secondaryBorder' => $this->settings->get('settings::royal:secondaryBorder', '#42425B'),
            'secondaryBackground' => $this->settings->get('settings::royal:secondaryBackground', '#2B2B40'),
            'gray50' => $this->settings->get('settings::royal:gray50', '#F4F4F4'),
            'gray100' => $this->settings->get('settings::royal:gray100', '#D5D5DB'),
            'gray200' => $this->settings->get('settings::royal:gray200', '#B2B2C1'),
            'gray300' => $this->settings->get('settings::royal:gray300', '#8282A4'),
            'gray400' => $this->settings->get('settings::royal:gray400', '#5E5E7F'),
            'gray500' => $this->settings->get('settings::royal:gray500', '#42425B'),
            'gray600' => $this->settings->get('settings::royal:gray600', '#2B2B40'),
            'gray700' => $this->settings->get('settings::royal:gray700', '#1D1D37'),
            'gray800' => $this->settings->get('settings::royal:gray800', '#0B0D2A'),
            'gray900' => $this->settings->get('settings::royal:gray900', '#040519'),
            'lightmode_primary' => $this->settings->get('settings::royal:lightmode_primary', '#4A35CF'),
            'lightmode_successText' => $this->settings->get('settings::royal:lightmode_successText', '#E1FFD8'),
            'lightmode_successBorder' => $this->settings->get('settings::royal:lightmode_successBorder', '#56AA2B'),
            'lightmode_successBackground' => $this->settings->get('settings::royal:lightmode_successBackground', '#3D8F1F'),
            'lightmode_dangerText' => $this->settings->get('settings::royal:lightmode_dangerText', '#FFD8D8'),
            'lightmode_dangerBorder' => $this->settings->get('settings::royal:lightmode_dangerBorder', '#AA2A2A'),
            'lightmode_dangerBackground' => $this->settings->get('settings::royal:lightmode_dangerBackground', '#8F1F20'),
            'lightmode_secondaryText' => $this->settings->get('settings::royal:lightmode_secondaryText', '#46464D'),
            'lightmode_secondaryBorder' => $this->settings->get('settings::royal:lightmode_secondaryBorder', '#C0C0D3'),
            'lightmode_secondaryBackground' => $this->settings->get('settings::royal:lightmode_secondaryBackground', '#A6A7BD'),
            'lightmode_gray50' => $this->settings->get('settings::royal:lightmode_gray50', '#141415'),
            'lightmode_gray100' => $this->settings->get('settings::royal:lightmode_gray100', '#27272C'),
            'lightmode_gray200' => $this->settings->get('settings::royal:lightmode_gray200', '#46464D'),
            'lightmode_gray300' => $this->settings->get('settings::royal:lightmode_gray300', '#626272'),
            'lightmode_gray400' => $this->settings->get('settings::royal:lightmode_gray400', '#757689'),
            'lightmode_gray500' => $this->settings->get('settings::royal:lightmode_gray500', '#A6A7BD'),
            'lightmode_gray600' => $this->settings->get('settings::royal:lightmode_gray600', '#C0C0D3'),
            'lightmode_gray700' => $this->settings->get('settings::royal:lightmode_gray700', '#E7E7EF'),
            'lightmode_gray800' => $this->settings->get('settings::royal:lightmode_gray800', '#F0F1F5'),
            'lightmode_gray900' => $this->settings->get('settings::royal:lightmode_gray900', '#FFFFFF'),
        ]);
    }

    public function store(RoyalColorsRequest $request)
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }
        $this->alert->success('Theme settings have been updated successfully.')->flash();
        return redirect()->route('admin.royal.colors');
    }
}