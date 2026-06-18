<?php

namespace RoyalPanel\Http\Requests\Api\Application\Locations;

use RoyalPanel\Models\Location;

class UpdateLocationRequest extends StoreLocationRequest
{
    /**
     * Rules to validate this request against.
     */
    public function rules(): array
    {
        $locationId = $this->route()->parameter('location')->id; // @phpstan-ignore property.nonObject

        return collect(Location::getRulesForUpdate($locationId))->only([
            'short',
            'long',
        ])->toArray();
    }
}
