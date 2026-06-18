<?php

namespace RoyalPanel\Http\Requests\Api\Application\Users;

use RoyalPanel\Services\Acl\Api\AdminAcl as Acl;
use RoyalPanel\Http\Requests\Api\Application\ApplicationApiRequest;

class GetUsersRequest extends ApplicationApiRequest
{
    protected ?string $resource = Acl::RESOURCE_USERS;

    protected int $permission = Acl::READ;
}
