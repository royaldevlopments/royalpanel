<?php

namespace RoyalPanel\Exceptions\Service\Allocation;

use RoyalPanel\Exceptions\DisplayException;

class InvalidPortMappingException extends DisplayException
{
    /**
     * InvalidPortMappingException constructor.
     */
    public function __construct(mixed $port)
    {
        parent::__construct(trans('exceptions.allocations.invalid_mapping', ['port' => $port]));
    }
}
