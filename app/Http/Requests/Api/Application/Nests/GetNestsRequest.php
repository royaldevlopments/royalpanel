<?php

namespace RoyalPanel\Http\Requests\Api\Application\Nests;

use RoyalPanel\Services\Acl\Api\AdminAcl;
use RoyalPanel\Http\Requests\Api\Application\ApplicationApiRequest;

class GetNestsRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_NESTS;

    protected int $permission = AdminAcl::READ;
}
