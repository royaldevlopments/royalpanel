<?php

namespace RoyalPanel\Http\Requests\Api\Application\Locations;

use RoyalPanel\Services\Acl\Api\AdminAcl;
use RoyalPanel\Http\Requests\Api\Application\ApplicationApiRequest;

class GetLocationsRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_LOCATIONS;

    protected int $permission = AdminAcl::READ;
}
