<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Schedules;

use RoyalPanel\Models\Permission;

class UpdateScheduleRequest extends StoreScheduleRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SCHEDULE_UPDATE;
    }
}
