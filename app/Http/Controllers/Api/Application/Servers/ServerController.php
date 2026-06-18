<?php

namespace RoyalPanel\Http\Controllers\Api\Application\Servers;

use Illuminate\Http\Response;
use RoyalPanel\Models\Server;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use RoyalPanel\Services\Servers\ServerCreationService;
use RoyalPanel\Services\Servers\ServerDeletionService;
use RoyalPanel\Transformers\Api\Application\ServerTransformer;
use RoyalPanel\Http\Requests\Api\Application\Servers\GetServerRequest;
use RoyalPanel\Http\Requests\Api\Application\Servers\GetServersRequest;
use RoyalPanel\Http\Requests\Api\Application\Servers\ServerWriteRequest;
use RoyalPanel\Http\Requests\Api\Application\Servers\StoreServerRequest;
use RoyalPanel\Http\Controllers\Api\Application\ApplicationApiController;

class ServerController extends ApplicationApiController
{
    /**
     * ServerController constructor.
     */
    public function __construct(
        private ServerCreationService $creationService,
        private ServerDeletionService $deletionService,
    ) {
        parent::__construct();
    }

    /**
     * Return all the servers that currently exist on the Panel.
     */
    public function index(GetServersRequest $request): array
    {
        $servers = QueryBuilder::for(Server::query())
            ->allowedFilters(['uuid', 'uuidShort', 'name', 'description', 'image', 'external_id'])
            ->allowedSorts(['id', 'uuid'])
            ->paginate($request->query('per_page') ?? 50);

        return $this->fractal->collection($servers)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * Create a new server on the system.
     *
     * @throws \Throwable
     * @throws \Illuminate\Validation\ValidationException
     * @throws \RoyalPanel\Exceptions\DisplayException
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     * @throws \RoyalPanel\Exceptions\Service\Deployment\NoViableAllocationException
     * @throws \RoyalPanel\Exceptions\Service\Deployment\NoViableNodeException
     */
    public function store(StoreServerRequest $request): JsonResponse
    {
        $server = $this->creationService->handle($request->validated(), $request->getDeploymentObject());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->respond(201);
    }

    /**
     * Show a single server transformed for the application API.
     */
    public function view(GetServerRequest $request, Server $server): array
    {
        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * Deletes a server.
     *
     * @throws \RoyalPanel\Exceptions\DisplayException
     */
    public function delete(ServerWriteRequest $request, Server $server, string $force = ''): Response
    {
        $this->deletionService->withForce($force === 'force')->handle($server);

        return $this->returnNoContent();
    }
}
