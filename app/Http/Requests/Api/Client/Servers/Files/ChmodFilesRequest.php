<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Files;

use RoyalPanel\Models\Permission;
use RoyalPanel\Contracts\Http\ClientPermissionsRequest;
use RoyalPanel\Http\Requests\Api\Client\ClientApiRequest;

class ChmodFilesRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_UPDATE;
    }

    public function rules(): array
    {
        return [
            'root' => 'required|nullable|string',
            'files' => 'required|array',
            'files.*.file' => 'required|string',
            'files.*.mode' => 'required|numeric',
        ];
    }
}
