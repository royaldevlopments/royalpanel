<?php

namespace RoyalPanel\Providers;

use RoyalPanel\Models\User;
use RoyalPanel\Models\Server;
use RoyalPanel\Models\Subuser;
use RoyalPanel\Models\EggVariable;
use RoyalPanel\Observers\UserObserver;
use RoyalPanel\Observers\ServerObserver;
use RoyalPanel\Observers\SubuserObserver;
use RoyalPanel\Listeners\TwoFactorListener;
use RoyalPanel\Listeners\RevocationListener;
use RoyalPanel\Observers\EggVariableObserver;
use RoyalPanel\Listeners\AuthenticationListener;
use RoyalPanel\Events\Server\Installed as ServerInstalledEvent;
use RoyalPanel\Notifications\ServerInstalled as ServerInstalledNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        ServerInstalledEvent::class => [ServerInstalledNotification::class],
    ];

    protected $subscribe = [
        AuthenticationListener::class,
        RevocationListener::class,
        TwoFactorListener::class,
    ];

    protected static $shouldDiscoverEvents = false;

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        User::observe(UserObserver::class);
        Server::observe(ServerObserver::class);
        Subuser::observe(SubuserObserver::class);
        EggVariable::observe(EggVariableObserver::class);
    }
}
