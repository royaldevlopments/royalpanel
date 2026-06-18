<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Databases;

use RoyalPanel\Models\Permission;
use RoyalPanel\Contracts\Http\ClientPermissionsRequest;
use RoyalPanel\Http\Requests\Api\Client\ClientApiRequest;

class GetDatabasesRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_READ;
    }
}
