<?php

namespace RoyalPanel\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use RoyalPanel\Models\Server;
use RoyalPanel\Facades\Activity;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use RoyalPanel\Repositories\Wings\DaemonCommandRepository;
use RoyalPanel\Http\Controllers\Api\Client\ClientApiController;
use RoyalPanel\Http\Requests\Api\Client\Servers\SendCommandRequest;
use RoyalPanel\Exceptions\Http\Connection\DaemonConnectionException;

class CommandController extends ClientApiController
{
    public function __construct(private DaemonCommandRepository $repository)
    {
        parent::__construct();
    }

    public function index(SendCommandRequest $request, Server $server): Response
    {
        if ($server->isMaintenanceMode()) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException(
                'Server is in maintenance mode. Commands are disabled.'
            );
        }

        try {
            $this->repository->setServer($server)->send($request->input('command'));
        } catch (DaemonConnectionException $exception) {
            $previous = $exception->getPrevious();
            throw $previous instanceof HttpException ? $previous : $exception;
        }

        Activity::event('server:command')->log();

        return $this->returnNoContent();
    }
}
