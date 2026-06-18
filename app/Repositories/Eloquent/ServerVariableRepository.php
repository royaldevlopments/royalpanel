<?php

namespace RoyalPanel\Repositories\Eloquent;

use RoyalPanel\Models\ServerVariable;
use RoyalPanel\Contracts\Repository\ServerVariableRepositoryInterface;

class ServerVariableRepository extends EloquentRepository implements ServerVariableRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return ServerVariable::class;
    }
}
