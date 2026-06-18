<?php

namespace RoyalPanel\Http\Requests\Admin;

use RoyalPanel\Models\Location;

class LocationFormRequest extends AdminFormRequest
{
    /**
     * Set up the validation rules to use for these requests.
     */
    public function rules(): array
    {
        if ($this->method() === 'PATCH') {
            return Location::getRulesForUpdate($this->route()->parameter('location')->id); // @phpstan-ignore property.nonObject
        }

        return Location::getRules();
    }
}
