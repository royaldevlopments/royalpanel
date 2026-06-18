<?php

namespace RoyalPanel\Contracts\Criteria;

use Illuminate\Database\Eloquent\Model;
use RoyalPanel\Repositories\Repository;

interface CriteriaInterface
{
    /**
     * Apply selected criteria to a repository call.
     */
    public function apply(Model $model, Repository $repository): mixed;
}
