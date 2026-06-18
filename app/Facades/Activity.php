<?php

namespace RoyalPanel\Facades;

use Illuminate\Support\Facades\Facade;
use RoyalPanel\Services\Activity\ActivityLogService;

class Activity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActivityLogService::class;
    }
}
