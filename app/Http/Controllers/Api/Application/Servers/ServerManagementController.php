<?php

namespace RoyalPanel\Http\Controllers\Api\Application\Servers;

use Illuminate\Http\Response;
use RoyalPanel\Models\Server;
use RoyalPanel\Services\Servers\SuspensionService;
use RoyalPanel\Services\Servers\ReinstallServerService;
use RoyalPanel\Http\Requests\Api\Application\Servers\ServerWriteRequest;
use RoyalPanel\Http\Controllers\Api\Application\ApplicationApiController;

class ServerManagementController extends ApplicationApiController
{
    /**
     * ServerManagementController constructor.
     */
    public function __construct(
        private ReinstallServerService $reinstallServerService,
        private SuspensionService $suspensionService,
    ) {
        parent::__construct();
    }

    /**
     * Suspend a server on the Panel.
     *
     * @throws \Throwable
     */
    public function suspend(ServerWriteRequest $request, Server $server): Response
    {
        $this->suspensionService->toggle($server);

        return $this->returnNoContent();
    }

    /**
     * Unsuspend a server on the Panel.
     *
     * @throws \Throwable
     */
    public function unsuspend(ServerWriteRequest $request, Server $server): Response
    {
        $this->suspensionService->toggle($server, SuspensionService::ACTION_UNSUSPEND);

        return $this->returnNoContent();
    }

    /**
     * Mark a server as needing to be reinstalled.
     *
     * @throws \RoyalPanel\Exceptions\DisplayException
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function reinstall(ServerWriteRequest $request, Server $server): Response
    {
        $this->reinstallServerService->handle($server);

        return $this->returnNoContent();
    }
}
