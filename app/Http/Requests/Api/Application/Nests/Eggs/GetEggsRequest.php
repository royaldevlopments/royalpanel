<?php

namespace RoyalPanel\Http\Requests\Api\Application\Nests\Eggs;

use RoyalPanel\Services\Acl\Api\AdminAcl;
use RoyalPanel\Http\Requests\Api\Application\ApplicationApiRequest;

class GetEggsRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_EGGS;

    protected int $permission = AdminAcl::READ;
}
