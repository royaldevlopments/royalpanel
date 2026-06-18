<?php

namespace RoyalPanel\Contracts\Repository;

use RoyalPanel\Models\Nest;
use Illuminate\Database\Eloquent\Collection;

interface NestRepositoryInterface extends RepositoryInterface
{
    /**
     * Return a nest or all nests with their associated eggs and variables.
     *
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithEggs(?int $id = null): Collection|Nest;

    /**
     * Return a nest or all nests and the count of eggs and servers for that nest.
     *
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithCounts(?int $id = null): Collection|Nest;

    /**
     * Return a nest along with its associated eggs and the servers relation on those eggs.
     *
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function getWithEggServers(int $id): Nest;
}
