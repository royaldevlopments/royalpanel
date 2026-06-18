<?php

namespace RoyalPanel\Facades;

use Illuminate\Support\Facades\Facade;
use RoyalPanel\Services\Activity\ActivityLogTargetableService;

/**
 * @mixin \RoyalPanel\Services\Activity\ActivityLogTargetableService
 */
class LogTarget extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActivityLogTargetableService::class;
    }
}
