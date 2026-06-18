<?php

namespace RoyalPanel\Http\Requests\Api\Application\Nodes;

use RoyalPanel\Services\Acl\Api\AdminAcl;
use RoyalPanel\Http\Requests\Api\Application\ApplicationApiRequest;

class DeleteNodeRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_NODES;

    protected int $permission = AdminAcl::WRITE;
}
