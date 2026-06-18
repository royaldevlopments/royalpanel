<?php

namespace RoyalPanel\Http\Controllers\Api\Application\Servers;

use RoyalPanel\Models\Server;
use RoyalPanel\Services\Servers\BuildModificationService;
use RoyalPanel\Services\Servers\DetailsModificationService;
use RoyalPanel\Transformers\Api\Application\ServerTransformer;
use RoyalPanel\Http\Controllers\Api\Application\ApplicationApiController;
use RoyalPanel\Http\Requests\Api\Application\Servers\UpdateServerDetailsRequest;
use RoyalPanel\Http\Requests\Api\Application\Servers\UpdateServerBuildConfigurationRequest;

class ServerDetailsController extends ApplicationApiController
{
    /**
     * ServerDetailsController constructor.
     */
    public function __construct(
        private BuildModificationService $buildModificationService,
        private DetailsModificationService $detailsModificationService,
    ) {
        parent::__construct();
    }

    /**
     * Update the details for a specific server.
     *
     * @throws \RoyalPanel\Exceptions\DisplayException
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function details(UpdateServerDetailsRequest $request, Server $server): array
    {
        $updated = $this->detailsModificationService->returnUpdatedModel()->handle(
            $server,
            $request->validated()
        );

        return $this->fractal->item($updated)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }

    /**
     * Update the build details for a specific server.
     *
     * @throws \RoyalPanel\Exceptions\DisplayException
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \RoyalPanel\Exceptions\Repository\RecordNotFoundException
     */
    public function build(UpdateServerBuildConfigurationRequest $request, Server $server): array
    {
        $server = $this->buildModificationService->handle($server, $request->validated());

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->toArray();
    }
}
