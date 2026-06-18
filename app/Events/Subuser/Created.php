<?php

namespace RoyalPanel\Events\Subuser;

use RoyalPanel\Events\Event;
use RoyalPanel\Models\Subuser;
use Illuminate\Queue\SerializesModels;

class Created extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Subuser $subuser)
    {
    }
}
