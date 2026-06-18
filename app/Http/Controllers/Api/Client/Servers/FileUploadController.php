<?php

namespace RoyalPanel\Http\Controllers\Api\Client\Servers;

use Carbon\CarbonImmutable;
use RoyalPanel\Models\User;
use RoyalPanel\Enum\JwtScope;
use RoyalPanel\Models\Server;
use Illuminate\Http\JsonResponse;
use RoyalPanel\Services\Nodes\NodeJWTService;
use RoyalPanel\Http\Controllers\Api\Client\ClientApiController;
use RoyalPanel\Http\Requests\Api\Client\Servers\Files\UploadFileRequest;

class FileUploadController extends ClientApiController
{
    /**
     * FileUploadController constructor.
     */
    public function __construct(
        private NodeJWTService $jwtService,
    ) {
        parent::__construct();
    }

    /**
     * Returns an url where files can be uploaded to.
     */
    public function __invoke(UploadFileRequest $request, Server $server): JsonResponse
    {
        return new JsonResponse([
            'object' => 'signed_url',
            'attributes' => [
                'url' => $this->getUploadUrl($server, $request->user()),
            ],
        ]);
    }

    /**
     * Returns an url where files can be uploaded to.
     */
    protected function getUploadUrl(Server $server, User $user): string
    {
        $token = $this->jwtService
            ->setExpiresAt(CarbonImmutable::now()->addMinutes(15))
            ->setUser($user)
            ->setClaims(['server_uuid' => $server->uuid])
            ->setScopes(JwtScope::FileUpload)
            ->handle($server->node, $user->id . $server->uuid);

        return sprintf(
            '%s/upload/file?token=%s',
            $server->node->getConnectionAddress(),
            $token->toString()
        );
    }
}
