<?php

namespace RoyalPanel\Repositories\Eloquent;

use RoyalPanel\Models\User;
use RoyalPanel\Contracts\Repository\UserRepositoryInterface;

class UserRepository extends EloquentRepository implements UserRepositoryInterface
{
    /**
     * Return the model backing this repository.
     */
    public function model(): string
    {
        return User::class;
    }
}
