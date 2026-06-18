<?php

namespace RoyalPanel\Events\Server;

use RoyalPanel\Events\Event;
use RoyalPanel\Models\Server;
use Illuminate\Queue\SerializesModels;

class Installed extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Server $server)
    {
    }
}
