<!DOCTYPE html>
<html>
    <head>
        <title>{{ config('app.name', 'Royal Panel') }}</title>
        @yield('head')

        @section('meta')
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            
            <!-- meta data -->

            <meta name="theme-color" content="{{ $siteConfiguration['royal']['meta_color'] }}"/>
            <link rel="icon" type="image/x-icon" href="{{ $siteConfiguration['royal']['meta_favicon'] }}">

            <meta name="title" content="{{ $siteConfiguration['royal']['meta_title'] }}" />
            <meta name="description" content="{{ $siteConfiguration['royal']['meta_description'] }}" />

            <meta property="og:type" content="website" />
            <meta property="og:url" content="{{config('app.url', 'https://localhost')}}" />
            <meta property="og:title" content="{{ $siteConfiguration['royal']['meta_title'] }}" />
            <meta property="og:description" content="{{ $siteConfiguration['royal']['meta_description'] }}" />
            <meta property="og:image" content="{{ $siteConfiguration['royal']['meta_image'] }}" />

            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:url" content="{{config('app.url', 'https://localhost')}}" />
            <meta property="twitter:title" content="{{ $siteConfiguration['royal']['meta_title'] }}" />
            <meta property="twitter:description" content="{{ $siteConfiguration['royal']['meta_description'] }}" />
            <meta property="twitter:image" content="{{ $siteConfiguration['royal']['meta_image'] }}" />

            <!-- PWA -->
            <link rel="manifest" href="/manifest.json">
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
            <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Royal Panel') }}">
            <link rel="apple-touch-icon" href="/favicons/apple-touch-icon.png">

            <!-- meta data -->
            <!--
            <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png?v=%%__USER__%%">
            <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
            <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
            <link rel="manifest" href="/favicons/manifest.json">
            <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
            <link rel="shortcut icon" href="/favicons/favicon.ico">
            <meta name="msapplication-config" content="/favicons/browserconfig.xml">
        -->
        @show

        @section('user-data')
            @if(!is_null(Auth::user()))
                <script>
                    window.RoyalPanelUser = {!! json_encode(Auth::user()->toVueObject()) !!};
                    window.PterodactylUser = window.RoyalPanelUser;
                </script>
            @endif
            @if(!empty($siteConfiguration))
                <script>
                    window.SiteConfiguration = {!! json_encode($siteConfiguration) !!};
                </script>
            @endif
        @show
        <style>
            @import url('{{
                    [
                        'poppins' => '//fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
                        'dm_sans' => '//fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap',
                        'roboto' => '//fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap',
                        'sciencegothic' => '//fonts.googleapis.com/css2?family=Science+Gothic:wght@300;400;500;700&display=swap',
                        'inter' => '//fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
                        'montserrat' => '//fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap',
                        'open_sans' => '//fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap',
                        'lato' => '//fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap',
                        'nunito' => '//fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap',
                        'oswald' => '//fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;700&display=swap',
                        'playfair' => '//fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap',
                        'source_sans' => '//fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap',
                        'quicksand' => '//fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap',
                        'manrope' => '//fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&display=swap',
                        'space_grotesk' => '//fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap',
                    ][$siteConfiguration['royal']['font']] ?? ''
                }}');
                
            @import url('//fonts.googleapis.com/css?family=Rubik:300,400,500&display=swap');
            @import url('//fonts.googleapis.com/css?family=IBM+Plex+Mono|IBM+Plex+Sans:500&display=swap');
            
            :root{
                <?php if ($siteConfiguration['royal']['borderInput'] === 'true') {
                    echo '--borderInput: 1px solid;
';  
                }?>
                --radiusBox: {{ $siteConfiguration['royal']['radiusBox'] }}px;
                --radiusInput: {{ $siteConfiguration['royal']['radiusInput'] }}px;

                --fontFamily: '{{
                    [
                        'poppins' => 'Poppins',
                        'dm_sans' => 'IBM Plex Sans',
                        'roboto' => 'Roboto',
                        'sciencegothic' => 'Science Gothic',
                        'inter' => 'Inter',
                        'montserrat' => 'Montserrat',
                        'open_sans' => 'Open Sans',
                        'lato' => 'Lato',
                        'nunito' => 'Nunito',
                        'oswald' => 'Oswald',
                        'playfair' => 'Playfair Display',
                        'source_sans' => 'Source Sans Pro',
                        'quicksand' => 'Quicksand',
                        'manrope' => 'Manrope',
                        'space_grotesk' => 'Space Grotesk',
                    ][$siteConfiguration['royal']['font']] ?? ''
                }}';
            }

            <?php if ($siteConfiguration['royal']['defaultMode'] === 'darkmode') {
                echo ':root';
            } else {
                echo '.lightmode';
            }?>{
                --image: url({{ $siteConfiguration['royal']['backgroundImage'] }});
                --primary: {{ $siteConfiguration['royal']['primary'] }};

                --successText: {{ $siteConfiguration['royal']['successText'] }};
                --successBorder: {{ $siteConfiguration['royal']['successBorder'] }};
                --successBackground: {{ $siteConfiguration['royal']['successBackground'] }};

                --dangerText: {{ $siteConfiguration['royal']['dangerText'] }};
                --dangerBorder: {{ $siteConfiguration['royal']['dangerBorder'] }};
                --dangerBackground: {{ $siteConfiguration['royal']['dangerBackground'] }}; 

                --secondaryText: {{ $siteConfiguration['royal']['secondaryText'] }};
                --secondaryBorder: {{ $siteConfiguration['royal']['secondaryBorder'] }};
                --secondaryBackground: {{ $siteConfiguration['royal']['secondaryBackground'] }};

                --gray50: {{ $siteConfiguration['royal']['gray50'] }};
                --gray100: {{ $siteConfiguration['royal']['gray100'] }};
                --gray200: {{ $siteConfiguration['royal']['gray200'] }};
                --gray300: {{ $siteConfiguration['royal']['gray300'] }};
                --gray400: {{ $siteConfiguration['royal']['gray400'] }};
                --gray500: {{ $siteConfiguration['royal']['gray500'] }};
                --gray600: {{ $siteConfiguration['royal']['gray600'] }};
                --gray700: color-mix(in srgb, {{ $siteConfiguration['royal']['gray700'] }} {{ $siteConfiguration['royal']['backdropPercentage'] }}%, transparent);
                --gray800: {{ $siteConfiguration['royal']['gray800'] }};
                --gray900: {{ $siteConfiguration['royal']['gray900'] }};

                --gray700-default: {{ $siteConfiguration['royal']['gray700'] }};
                --fallBackGray: color-mix(in srgb, {{ $siteConfiguration['royal']['gray700'] }} {{ $siteConfiguration['royal']['backdropPercentage'] }}%, transparent);
            }
            <?php if ($siteConfiguration['royal']['defaultMode'] !== 'darkmode') {
                echo ':root';
            } else {
                echo '.lightmode';
            }?>{
                --image: url({{ $siteConfiguration['royal']['backgroundImageLight'] }});
                --primary: {{ $siteConfiguration['royal']['lightmode_primary'] }};

                --successText: {{ $siteConfiguration['royal']['lightmode_successText'] }};
                --successBorder: {{ $siteConfiguration['royal']['lightmode_successBorder'] }};
                --successBackground: {{ $siteConfiguration['royal']['lightmode_successBackground'] }};

                --dangerText: {{ $siteConfiguration['royal']['lightmode_dangerText'] }};
                --dangerBorder: {{ $siteConfiguration['royal']['lightmode_dangerBorder'] }};
                --dangerBackground: {{ $siteConfiguration['royal']['lightmode_dangerBackground'] }}; 

                --secondaryText: {{ $siteConfiguration['royal']['lightmode_secondaryText'] }};
                --secondaryBorder: {{ $siteConfiguration['royal']['lightmode_secondaryBorder'] }};
                --secondaryBackground: {{ $siteConfiguration['royal']['lightmode_secondaryBackground'] }};

                --gray50: {{ $siteConfiguration['royal']['lightmode_gray50'] }};
                --gray100: {{ $siteConfiguration['royal']['lightmode_gray100'] }};
                --gray200: {{ $siteConfiguration['royal']['lightmode_gray200'] }};
                --gray300: {{ $siteConfiguration['royal']['lightmode_gray300'] }};
                --gray400: {{ $siteConfiguration['royal']['lightmode_gray400'] }};
                --gray500: {{ $siteConfiguration['royal']['lightmode_gray500'] }};
                --gray600: {{ $siteConfiguration['royal']['lightmode_gray600'] }}; 
                --gray700: color-mix(in srgb, {{ $siteConfiguration['royal']['lightmode_gray700'] }} {{ $siteConfiguration['royal']['backdropPercentage'] }}%, transparent);
                --gray800: {{ $siteConfiguration['royal']['lightmode_gray800'] }};
                --gray900: {{ $siteConfiguration['royal']['lightmode_gray900'] }};

                --gray700-default: {{ $siteConfiguration['royal']['lightmode_gray700'] }};
            }

            <?php if ($siteConfiguration['royal']['backdrop'] === 'true') {
                echo '.backdrop{border:1px solid;border-color:var(--gray600)!important;backdrop-filter:blur(16px);}';
            }?>

            .privacy .privacy-blur:not(:focus){
                color: transparent !important;
                text-shadow: 0 0 5px color-mix(in srgb, var(--gray200) 50%, transparent) !important;
            }
        </style>

        <link rel="stylesheet" href="/themes/neon-gaming.css">

        @yield('assets')

        @include('layouts.scripts')
    </head>
    <body class="{{ $css['body'] ?? 'bg-neutral-50' }}">
        @section('content')
            @yield('above-container')
            @yield('container')
            @yield('below-container')
        @show
        @section('scripts')
            {!! $asset->js('main.js') !!}
        @show

        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((registration) => {
                            console.log('SW registered:', registration.scope);
                        })
                        .catch((error) => {
                            console.log('SW registration failed:', error);
                        });
                });
            }
        </script>
    </body>
</html>
