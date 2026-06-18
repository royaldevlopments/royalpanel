<?php

namespace RoyalPanel\Exceptions\Service;

use Illuminate\Http\Response;
use RoyalPanel\Exceptions\DisplayException;

class HasActiveServersException extends DisplayException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
