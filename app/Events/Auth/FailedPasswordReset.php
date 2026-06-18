<?php

namespace RoyalPanel\Events\Auth;

use RoyalPanel\Events\Event;
use Illuminate\Queue\SerializesModels;

class FailedPasswordReset extends Event
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public string $ip, public string $email)
    {
    }
}
