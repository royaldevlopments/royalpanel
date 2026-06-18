<?php

namespace RoyalPanel\Http\Controllers\Api\Application\Servers;

use RoyalPanel\Models\User;
use RoyalPanel\Models\Server;
use RoyalPanel\Services\Servers\StartupModificationService;
use RoyalPanel\Transformers\Api\Application\ServerTransformer;
use RoyalPanel\Http\Controllers\Api\Application\ApplicationApiController;
use RoyalPanel\Http\Requests\Api\Application\Servers\UpdateServerStartupRequest;

class StartupController extends ApplicationApiController
{
    /**
     * StartupController constructor.
     */
    public function __construct(private StartupModificationService $modificationService)
    {
        parent::__construct();
    }

    /**
     * Update the startup and environment settings for a specific server.
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \RoyalPanel\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function index(UpdateServerStartupRequest $request, Server $server): array
    {
        $server = $this->modificationService
            ->setUserLevel(User::USER_LEVEL_ADMIN)
            ->handle($server, $request->validated());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }
}
