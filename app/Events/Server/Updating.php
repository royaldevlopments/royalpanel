<?php

namespace RoyalPanel\Events\Server;

use RoyalPanel\Events\Event;
use RoyalPanel\Models\Server;
use Illuminate\Queue\SerializesModels;

class Updating extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Server $server)
    {
    }
}
