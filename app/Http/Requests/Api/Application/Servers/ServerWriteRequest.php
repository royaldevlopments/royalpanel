<?php

namespace RoyalPanel\Http\Requests\Api\Application\Servers;

use RoyalPanel\Services\Acl\Api\AdminAcl;
use RoyalPanel\Http\Requests\Api\Application\ApplicationApiRequest;

class ServerWriteRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_SERVERS;

    protected int $permission = AdminAcl::WRITE;
}
