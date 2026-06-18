<?php

namespace RoyalPanel\Events\Auth;

use RoyalPanel\Models\User;
use RoyalPanel\Events\Event;

class DirectLogin extends Event
{
    public function __construct(public User $user, public bool $remember)
    {
    }
}
