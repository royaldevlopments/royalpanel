<?php

namespace RoyalPanel\Repositories\Eloquent;

use RoyalPanel\Models\RecoveryToken;

class RecoveryTokenRepository extends EloquentRepository
{
    public function model(): string
    {
        return RecoveryToken::class;
    }
}
