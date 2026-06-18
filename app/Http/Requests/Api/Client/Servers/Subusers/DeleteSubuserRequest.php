<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Subusers;

use RoyalPanel\Models\Permission;

class DeleteSubuserRequest extends SubuserRequest
{
    public function permission(): string
    {
        return Permission::ACTION_USER_DELETE;
    }
}
