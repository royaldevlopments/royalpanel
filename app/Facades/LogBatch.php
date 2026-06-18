<?php

namespace RoyalPanel\Facades;

use Illuminate\Support\Facades\Facade;
use RoyalPanel\Services\Activity\ActivityLogBatchService;

class LogBatch extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ActivityLogBatchService::class;
    }
}
