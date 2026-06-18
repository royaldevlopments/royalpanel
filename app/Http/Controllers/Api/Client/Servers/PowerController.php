<?php

namespace RoyalPanel\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use RoyalPanel\Models\Server;
use RoyalPanel\Facades\Activity;
use GuzzleHttp\Exception\BadResponseException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use RoyalPanel\Repositories\Wings\DaemonPowerRepository;
use RoyalPanel\Http\Controllers\Api\Client\ClientApiController;
use RoyalPanel\Http\Requests\Api\Client\Servers\SendPowerRequest;
use RoyalPanel\Exceptions\Http\Connection\DaemonConnectionException;
use RoyalPanel\Services\Discord\DiscordWebhookService;

class PowerController extends ClientApiController
{
    public function __construct(
        private DaemonPowerRepository $repository,
        private DiscordWebhookService $discord,
    ) {
        parent::__construct();
    }

    public function index(SendPowerRequest $request, Server $server): Response
    {
        if ($server->isMaintenanceMode()) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException(
                'Server is in maintenance mode. Power actions are disabled.'
            );
        }

        try {
            $this->repository->setServer($server)->send(
                $request->input('signal')
            );
        } catch (DaemonConnectionException $exception) {
            $previous = $exception->getPrevious();
            throw $previous instanceof HttpException ? $previous : $exception;
        }

        $signal = $request->input('signal');
        Activity::event(strtolower("server:power.{$signal}"))->log();

        $this->discord->send($server, "power.{$signal}");

        return $this->returnNoContent();
    }
}
