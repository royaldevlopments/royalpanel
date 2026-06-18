<?php

namespace RoyalPanel\Services\Locations;

use RoyalPanel\Models\Location;
use RoyalPanel\Contracts\Repository\LocationRepositoryInterface;

class LocationCreationService
{
    /**
     * LocationCreationService constructor.
     */
    public function __construct(protected LocationRepositoryInterface $repository)
    {
    }

    /**
     * Create a new location.
     *
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     */
    public function handle(array $data): Location
    {
        return $this->repository->create($data);
    }
}
