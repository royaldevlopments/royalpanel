<?php

namespace RoyalPanel\Events\User;

use RoyalPanel\Models\User;
use RoyalPanel\Events\Event;
use Illuminate\Queue\SerializesModels;

class Creating extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user)
    {
    }
}
