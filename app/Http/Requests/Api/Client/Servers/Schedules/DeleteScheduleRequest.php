<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Schedules;

use RoyalPanel\Models\Permission;

class DeleteScheduleRequest extends ViewScheduleRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SCHEDULE_DELETE;
    }
}
