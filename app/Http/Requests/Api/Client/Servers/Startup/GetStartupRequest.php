<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Startup;

use RoyalPanel\Models\Permission;
use RoyalPanel\Http\Requests\Api\Client\ClientApiRequest;

class GetStartupRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_STARTUP_READ;
    }
}
