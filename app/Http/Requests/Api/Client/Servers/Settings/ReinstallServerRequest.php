<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Settings;

use RoyalPanel\Models\Permission;
use RoyalPanel\Http\Requests\Api\Client\ClientApiRequest;

class ReinstallServerRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SETTINGS_REINSTALL;
    }
}
