<?php

namespace RoyalPanel\Http\ViewComposers;

use Illuminate\View\View;
use RoyalPanel\Services\Helpers\AssetHashService;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class AssetComposer
{
    /**
     * AssetComposer constructor.
     */
    public function __construct(private AssetHashService $assetHashService, private SettingsRepositoryInterface $settings)
    {
    }

    /**
     * Provide access to the asset service in the views.
     */
    public function compose(View $view): void
    {
        $view->with('asset', $this->assetHashService);
        $view->with('siteConfiguration', [
            'name' => config('app.name') ?? 'Royal Panel',
            'royal' => [
                /* GENERAL */
                'logo' => $this->settings->get('settings::royal:logo', '/royal/Royal.png'),
                'logoLight' => $this->settings->get('settings::royal:logoLight', '/royal/Royal.png'),
                'fullLogo' => $this->settings->get('settings::royal:fullLogo', false),
                'logoHeight' => $this->settings->get('settings::royal:logoHeight', '32'),
                'discord' => $this->settings->get('settings::royal:discord', '715281172422197300'),
                'support' => $this->settings->get('settings::royal:support', 'https://discord.gg/geCjrRbAwC'),

                /* ANNOUNCEMENT */
                'announcement' => $this->settings->get('settings::royal:announcement', false),
                'announcementColor' => $this->settings->get('settings::royal:announcementColor', '#16aaaa'),
                'announcementIcon' => $this->settings->get('settings::royal:announcementIcon', "megaphone"),
                'announcementMessage' => $this->settings->get('settings::royal:announcementMessage', 'We have a brand new game panel design!'),
                'announcementCta' => $this->settings->get('settings::royal:announcementCta', false),
                'announcementCtaTitle' => $this->settings->get('settings::royal:announcementCtaTitle', 'Buy now!'),
                'announcementCtaLink' => $this->settings->get('settings::royal:announcementCtaLink', '/'),
                'announcementDismissable' => $this->settings->get('settings::royal:announcementDismissable', false),

                /* STYLING */
                'pageTitle' => $this->settings->get('settings::royal:pageTitle', true),

                'background' => $this->settings->get('settings::royal:background', true),
                'backgroundImage' => $this->settings->get('settings::royal:backgroundImage', ''),
                'backgroundImageLight' => $this->settings->get('settings::royal:backgroundImageLight', ''),
                'loginBackground' => $this->settings->get('settings::royal:loginBackground', '/royal/background-login.png'),
                'backgroundFaded' => $this->settings->get('settings::royal:backgroundFaded', 'default'),

                'backdrop' => $this->settings->get('settings::royal:backdrop', false),
                'backdropPercentage' => $this->settings->get('settings::royal:backdropPercentage', 100),
                
                'radiusInput' => $this->settings->get('settings::royal:radiusInput', 7),
                'radiusBox' => $this->settings->get('settings::royal:radiusBox', 10),
                'borderInput' => $this->settings->get('settings::royal:borderInput', true),

                'flashMessage' => $this->settings->get('settings::royal:flashMessage', 1),

                'font' => $this->settings->get('settings::royal:font', 'default'),
                'icon' => $this->settings->get('settings::royal:icon', 'heroicons'),

                /* LAYOUTS */
                'layout' => $this->settings->get('settings::royal:layout', 1),
                'searchComponent' => $this->settings->get('settings::royal:searchComponent', 1),

                'logoPosition' => $this->settings->get('settings::royal:logoPosition', 1),
                'socialPosition' => $this->settings->get('settings::royal:socialPosition', 1),
                'loginLayout' => $this->settings->get('settings::royal:loginLayout', 1),
                'loginGradient' => $this->settings->get('settings::royal:loginGradient', 'true'),

                /* COMPONENTS */
                'serverRow' => $this->settings->get('settings::royal:serverRow', 1),
                'statsCards' => $this->settings->get('settings::royal:statsCards', 2),
                'sideGraphs' => $this->settings->get('settings::royal:sideGraphs', 2),
                'graphs' => $this->settings->get('settings::royal:graphs', 2),

                /* DASHBOARD WIDGETS */
                'dashboardWidgets' => json_decode($this->settings->get('settings::royal:dashboardWidgets', '[]'), true),

                /* COLORS DARKMODE */
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

                /* COLORS LIGHTMODE */
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

                /* META DATA */
                'meta_color' => $this->settings->get('settings::royal:meta_color', '#4a35cf'),
                'meta_title' => $this->settings->get('settings::royal:meta_title', 'Royal Panel'),
                'meta_description' => $this->settings->get('settings::royal:meta_description', 'Royal Panel'),
                'meta_image' => $this->settings->get('settings::royal:meta_image', '/royal/meta-tags.png'),
                'meta_favicon' => $this->settings->get('settings::royal:meta_favicon', '/royal/Royal.png'),

                /* EMAIL */
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

                /* Advanced */
                'profileType'       => $this->settings->get('settings::royal:profileType', 'gravatar'),
                'modeToggler'       => $this->settings->get('settings::royal:modeToggler', true),
                'langSwitch'        => $this->settings->get('settings::royal:langSwitch', true),
                'defaultLang'      => $this->settings->get('settings::royal:defaultLang', 'en'),
                'languageOptions'    => json_decode($this->settings->get('settings::royal:languageOptions', '[{"key":"en","name":"English"}]'), true) ?? [['key' => 'en', 'name' => 'English']],
                'ipFlag'            => $this->settings->get('settings::royal:ipFlag', true),
                'lowResourcesAlert' => $this->settings->get('settings::royal:lowResourcesAlert', false),
                'alertLink'         => $this->settings->get('settings::royal:alertLink', ''),
                'dashboardPage'       => $this->settings->get('settings::royal:dashboardPage', true),
                'registration'     => $this->settings->get('settings::royal:registration', false),
                'defaultMode' => $this->settings->get('settings::royal:defaultMode', 'darkmode'),
                'copyright' => $this->settings->get('settings::royal:copyright', 'Designed by Weijers.one'),
                'heroBadge' => $this->settings->get('settings::royal:heroBadge', 'Neon Gaming Network'),
                'heroTitle' => $this->settings->get('settings::royal:heroTitle', 'Power Your Game. Instantly.'),
                'heroTagline' => $this->settings->get('settings::royal:heroTagline', 'Blazing-fast servers with one-click deploy, real-time monitoring, and zero lag — built for competitive gaming.'),
                'enforceDiscordLink' => $this->settings->get('settings::royal:enforceDiscordLink', false),

                /* SOCIALS */
                'socials' => json_decode($this->settings->get('settings::royal:socials', '[]'), true),
                'socialButtons' => $this->settings->get('settings::royal:socialButtons', false),
                'discordBox' => $this->settings->get('settings::royal:discordBox', true),
            ],
            'locale' => config('app.locale') ?? 'en',
            'recaptcha' => [
                'enabled' => config('recaptcha.enabled', false),
                'method' => config('recaptcha.method', 'recaptcha'),
                'siteKey' => config('recaptcha.website_key') ?? '',
            ],
            'turnstile' => [
                'siteKey' => config('turnstile.site_key') ?? '',
            ],
        ]);
    }
}
