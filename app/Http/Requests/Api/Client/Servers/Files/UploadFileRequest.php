<?php

namespace RoyalPanel\Http\Requests\Api\Client\Servers\Files;

use RoyalPanel\Models\Permission;
use RoyalPanel\Http\Requests\Api\Client\ClientApiRequest;

class UploadFileRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_CREATE;
    }
}
