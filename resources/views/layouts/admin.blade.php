@php
    $__blueprintLoaded = false;
@endphp
@php
    use RoyalPanel\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;
    use RoyalPanel\BlueprintFramework\Services\PlaceholderService\BlueprintPlaceholderService;
@endphp
@if(file_exists(base_path('resources/views/blueprint/admin/admin.blade.php')))
  @include('blueprint.admin.admin')
  @php
    $settings = app()->make('RoyalPanel\Contracts\Repository\SettingsRepositoryInterface');
    $blueprint = app()->make(BlueprintExtensionLibrary::class, ['settings' => $settings]);
    $PlaceholderService = app()->make(BlueprintPlaceholderService::class);
    $__blueprintLoaded = true;
  @endphp
@endif
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{ config('app.name', 'Royal Panel') }} - @yield('title')</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="_token" content="{{ csrf_token() }}">

        <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="/favicons/manifest.json">
        <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
        <link rel="shortcut icon" href="/favicons/favicon.ico">
        <meta name="msapplication-config" content="/favicons/browserconfig.xml">
        <meta name="theme-color" content="#0e4688">

        @include('layouts.scripts')

        @section('scripts')
            {!! Theme::css('vendor/select2/select2.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/bootstrap/bootstrap.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/admin.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/adminlte/colors/skin-blue.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/sweetalert/sweetalert.min.css?t={cache-version}') !!}
            {!! Theme::css('vendor/animate/animate.min.css?t={cache-version}') !!}
            {!! Theme::css('css/royalpanel.css?t={cache-version}') !!}
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
            @yield('blueprint.import')
            <style>
                .panel-card { background:#1e1e32; border:1px solid #2a2a3e; border-radius:12px; margin-bottom:20px; overflow:hidden; }
                .panel-card-header { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; background:#2a2a3e; border-bottom:1px solid #333; }
                .panel-card-title { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:600; color:#e2e8f0; margin:0; }
                .panel-card-body { padding:16px 20px; font-size:13px; color:#94a3b8; }
                .badge-pill { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
                .badge-success { background:#22c55e; color:#fff; }
                .badge-warning { background:#f59e0b; color:#fff; }
                .page-header { margin:0 0 20px 0; border-bottom:1px solid #2a2a3e; padding-bottom:12px; }
                .page-header h1 { margin:0; font-size:22px; font-weight:600; color:#e2e8f0; display:flex; align-items:center; gap:10px; }
            </style>

            <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->
        @show

        <style>
            .royal{
                position: relative;
                font-weight: 500;
                color: #ffffff;
                overflow: hidden;
                z-index: 2;
            }
            .royal a{
                background-color: transparent !important;
            }
            .royal::after {
                opacity: 1;
                content: '';
                position: absolute;
                inset: 0;
                z-index: -1;
                background: #EEAECA;
                filter: blur(20px);
                background: linear-gradient(225deg,rgba(238, 174, 202, 1) 0%, rgba(125, 107, 242, 1) 25%, rgba(74, 53, 207, 1) 50%, rgba(53, 138, 207, 1) 75%, rgba(53, 207, 125, 1) 100%);
                animation: royalAnimationNav 10s infinite linear;
                transition: 0.3s;
            }
            .royal:hover::after{
                opacity: 0.7;
            }
            .royal span, .royal svg {
                font-weight: 500;
                color: #ffffff;
            }
            @keyframes royalAnimationNav {
                0%, 100% {
                    transform: scale(3) rotate(0deg) translateX(-25%) translateY(10px);
                }
                33% {
                    transform: scale(3) rotate(10deg) translateX(10px);
                }
                66% {
                    transform: scale(4) rotate(4deg) translateX(25%);
                }
            }

                        :root {
                --primary: #4a7c9e;
                --primary-border: #5a8fae;

                --text: #e0e0e0;
                --text-secondary: #a0a0b0;

                --box: #2b2b3a;
                --box-header: #2b2b3a;
                
                --active-border: #4a7c9e;
                --active: #1e3a4a;

                --input: #2b2b3a;
                --input-border: #3a3a4a;

                --sidebar: #1a1a28;

                --background: #1a2332;
            }
        </style>
    </head>
    <body class="hold-transition skin-blue fixed sidebar-mini">
        <div class="wrapper">
            <header class="main-header">
                <a href="{{ route('index') }}" class="logo">
                    <span>{{ config('app.name', 'Royal Panel') }}</span>
                </a>
                <nav class="navbar navbar-static-top">
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="user-menu">
                                <a href="{{ route('account') }}">
                                    <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(Auth::user()->email)) }}?s=160" class="user-image" alt="User Image">
                                    <span class="hidden-xs">{{ Auth::user()->name_first }} {{ Auth::user()->name_last }}</span>
                                </a>
                            </li>
                            @yield('blueprint.navigation')
                            <li>
                                <li><a href="{{ route('index') }}" data-toggle="tooltip" data-placement="bottom" title="Exit Admin Control"><i class="fa fa-server"></i></a></li>
                            </li>
                            <li>
                                <li><a href="{{ route('auth.logout') }}" id="logoutButton" data-toggle="tooltip" data-placement="bottom" title="Logout"><i class="fa fa-sign-out"></i></a></li>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <ul class="sidebar-menu">
                        <li class="header">BASIC ADMINISTRATION</li>
                        <li class="{{ Route::currentRouteName() !== 'admin.index' ?: 'active' }}">
                            <a href="{{ route('admin.index') }}">
                                <i data-lucide="gauge"></i> <span>Overview</span>
                            </a>
                        </li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.settings') ? 'active' : '' }}">
                            <a href="{{ route('admin.settings')}}">
                                <i data-lucide="cog"></i> <span>Settings</span>
                            </a>
                        </li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.royal') ? 'active' : '' }}">
                            <a href="{{ route('admin.royal')}}">
                                <i data-lucide="palette"></i><span>Royal Editor</span>
                            </a>
                        </li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.api') ? 'active' : '' }}">
                            <a href="{{ route('admin.api.index')}}">
                                <i data-lucide="key"></i> <span>Application API</span>
                            </a>
                        </li>
                        @if(Route::has('admin.extensions'))
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.extensions') ? 'active' : '' }}">
                            <a href="{{ route('admin.extensions') }}">
                                <i data-lucide="puzzle"></i> <span>Extensions</span>
                            </a>
                        </li>
                        @yield('blueprint.sidenav')
                        @endif
                        <li class="header">SECURITY</li>
                        <li class="{{ request()->routeIs('admin.security.index') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.index') }}">
                                <i data-lucide="shield"></i> <span>Security Center</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/security/shield*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.shield') }}">
                                <i data-lucide="octagon"></i> <span>Codenest Shield</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/security/blackhole*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.blackhole') }}">
                                <i data-lucide="radar"></i> <span>Blackhole Protection</span>
                            </a>
                        </li>
                        <li class="header">THREAT MANAGEMENT</li>
                        <li class="{{ request()->is('admin/security/geoip*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.geoip') }}">
                                <i data-lucide="earth-lock"></i> <span>Country Blocking</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/security/brute-force*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.brute_force') }}">
                                <i data-lucide="shield-off"></i> <span>Brute Force Protection</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/security/file-integrity*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.file_integrity') }}">
                                <i data-lucide="file-check"></i> <span>File Integrity Monitor</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/security/two-factor*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.two_factor') }}">
                                <i data-lucide="fingerprint"></i> <span>2FA Enforcement</span>
                            </a>
                        </li>
                        <li class="header">MONITORING</li>
                        <li class="{{ request()->is('admin/security/sessions*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.sessions') }}">
                                <i data-lucide="user-check"></i> <span>Session Manager</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/security/login-history*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.login_history') }}">
                                <i data-lucide="history"></i> <span>Login History</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/security/scanner*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.scanner') }}">
                                <i data-lucide="scan-eye"></i> <span>Security Scanner</span>
                            </a>
                        </li>
                        <li class="header">ADVANCED</li>
                        <li class="{{ request()->is('admin/security/waf*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.waf') }}">
                                <i data-lucide="shield-alert"></i> <span>WAF Rules</span>
                            </a>
                        </li>
                        <li class="{{ request()->is('admin/security/fail2ban*') ? 'active' : '' }}">
                            <a href="{{ route('admin.security.fail2ban') }}">
                                <i data-lucide="ban"></i> <span>Fail2Ban Integration</span>
                            </a>
                        </li>
                        <li class="header">MANAGEMENT</li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.databases') ? 'active' : '' }}">
                            <a href="{{ route('admin.databases') }}">
                                <i data-lucide="hard-drive"></i> <span>Databases</span>
                            </a>
                        </li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.locations') ? 'active' : '' }}">
                            <a href="{{ route('admin.locations') }}">
                                <i data-lucide="map-pin"></i> <span>Locations</span>
                            </a>
                        </li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.nodes') ? 'active' : '' }}">
                            <a href="{{ route('admin.nodes') }}">
                                <i data-lucide="cpu"></i> <span>Nodes</span>
                            </a>
                        </li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.servers') ? 'active' : '' }}">
                            <a href="{{ route('admin.servers') }}">
                                <i data-lucide="monitor"></i> <span>Servers</span>
                            </a>
                        </li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.users') ? 'active' : '' }}">
                            <a href="{{ route('admin.users') }}">
                                <i data-lucide="user-circle"></i> <span>Users</span>
                            </a>
                        </li>
                        <li class="header">SERVICE MANAGEMENT</li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.mounts') ? 'active' : '' }}">
                            <a href="{{ route('admin.mounts') }}">
                                <i data-lucide="briefcase"></i> <span>Mounts</span>
                            </a>
                        </li>
                        <li class="{{ starts_with(Route::currentRouteName(), 'admin.nests') ? 'active' : '' }}">
                            <a href="{{ route('admin.nests') }}">
                                <i data-lucide="layers"></i> <span>Nests</span>
                            </a>
                        </li>
                    </ul>
                </section>
            </aside>
            <div class="content-wrapper">
                <section class="content-header">
                    @yield('content-header')
                </section>
                <section class="content">
                    <div class="row">
                        <div class="col-xs-12">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    There was an error validating the data provided.<br><br>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            @foreach (Alert::getMessages() as $type => $messages)
                                @foreach ($messages as $message)
                                    <div class="alert alert-{{ $type }} alert-dismissable" role="alert">
                                        {{ $message }}
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    @yield('blueprint.wrappers')
                    @yield('content')
                </section>
            </div>
            <footer class="main-footer">
                <div class="pull-right small text-gray" style="margin-right:10px;margin-top:-7px;">
                    <strong><i class="fa fa-fw {{ $appIsGit ? 'fa-git-square' : 'fa-code-fork' }}"></i></strong> {{ $appVersion }}<br />
                    <strong><i class="fa fa-fw fa-clock-o"></i></strong> {{ round(microtime(true) - LARAVEL_START, 3) }}s
                </div>
                Copyright &copy; 2015 - {{ date('Y') }} <a href="https://github.com/royaldevlopments/">Royal Devlopments</a>.
            </footer>
        </div>
        @section('footer-scripts')
            <script src="/js/keyboard.polyfill.js" type="application/javascript"></script>
            <script>keyboardeventKeyPolyfill.polyfill();</script>

            {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/sweetalert/sweetalert.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap/bootstrap.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/slimscroll/jquery.slimscroll.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/adminlte/app.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/bootstrap-notify/bootstrap-notify.min.js?t={cache-version}') !!}
            {!! Theme::js('vendor/select2/select2.full.min.js?t={cache-version}') !!}
            {!! Theme::js('js/admin/functions.js?t={cache-version}') !!}
            <script src="/js/autocomplete.js" type="application/javascript"></script>

            <script src="https://unpkg.com/lucide@latest"></script>
            <script>
                lucide.createIcons();
            </script>

            @if(Auth::user()->root_admin)
                <script>
                    $('#logoutButton').on('click', function (event) {
                        event.preventDefault();

                        var that = this;
                        swal({
                            title: 'Do you want to log out?',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d9534f',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Log out'
                        }, function () {
                             $.ajax({
                                type: 'POST',
                                url: '{{ route('auth.logout') }}',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },complete: function () {
                                    window.location.href = '{{route('auth.login')}}';
                                }
                        });
                    });
                });
                </script>
            @endif

            <script>
                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                })
            </script>

            <script>
                function revToggle(header) {
                    var body = header.nextElementSibling;
                    var chevron = header.querySelector('.fa-chevron-down');
                    var collapsed = body.style.display === 'none';
                    body.style.display = collapsed ? '' : 'none';
                    if (chevron) chevron.style.transform = collapsed ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            </script>
        @show
    </body>
</html>

            .main-sidebar, .left-side {
                background-color: #1a1a28 !important;
            }
            .sidebar-menu > li.header {
                color: #d0e4f0 !important;
            }
            .sidebar-menu > li > a {
                color: #e0e8f0 !important;
            }
            .sidebar-menu > li > a:hover {
                background: rgba(0,0,0,0.1) !important;
            }
            .sidebar-menu > li.active > a {
                background: rgba(0,0,0,0.15) !important;
                border-left-color: #fff !important;
            }
            .skin-blue .sidebar a {
                color: #e0e8f0;
            }
            .skin-blue .treeview-menu > li > a {
                color: #c8d8e8;
            }
            .skin-blue .treeview-menu > li.active > a {
                color: #fff;
            }
