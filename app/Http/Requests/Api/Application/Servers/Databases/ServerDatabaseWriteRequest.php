<?php

namespace RoyalPanel\Http\Requests\Api\Application\Servers\Databases;

use RoyalPanel\Services\Acl\Api\AdminAcl;

class ServerDatabaseWriteRequest extends GetServerDatabasesRequest
{
    protected int $permission = AdminAcl::WRITE;
}
