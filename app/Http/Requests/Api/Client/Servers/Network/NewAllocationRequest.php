<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Network;

use RoyalPanel\Models\Permission;
use RoyalPanel\Http\Requests\Api\Client\ClientApiRequest;

class NewAllocationRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_ALLOCATION_CREATE;
    }
}
