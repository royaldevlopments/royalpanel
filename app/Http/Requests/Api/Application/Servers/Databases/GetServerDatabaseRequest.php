<?php

namespace RoyalPanel\Http\Requests\Api\Application\Servers\Databases;

use RoyalPanel\Services\Acl\Api\AdminAcl;
use RoyalPanel\Http\Requests\Api\Application\ApplicationApiRequest;

class GetServerDatabaseRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_SERVER_DATABASES;

    protected int $permission = AdminAcl::READ;
}
