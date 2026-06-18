<?php

namespace RoyalPanel\Http\Requests\Api\Application\Users;

use RoyalPanel\Services\Acl\Api\AdminAcl;
use RoyalPanel\Http\Requests\Api\Application\ApplicationApiRequest;

class DeleteUserRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_USERS;

    protected int $permission = AdminAcl::WRITE;
}
