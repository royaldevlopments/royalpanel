<?php

namespace RoyalPanel\Repositories\Wings;

use RoyalPanel\Models\Node;
use Lcobucci\JWT\Token\Plain;
use GuzzleHttp\Exception\GuzzleException;
use RoyalPanel\Exceptions\Http\Connection\DaemonConnectionException;

/**
 * @method \RoyalPanel\Repositories\Wings\DaemonTransferRepository setNode(\RoyalPanel\Models\Node $node)
 * @method \RoyalPanel\Repositories\Wings\DaemonTransferRepository setServer(\RoyalPanel\Models\Server $server)
 */
class DaemonTransferRepository extends DaemonRepository
{
    /**
     * @throws DaemonConnectionException
     */
    public function notify(Node $targetNode, Plain $token): void
    {
        try {
            $this->getHttpClient()->post(sprintf('/api/servers/%s/transfer', $this->server->uuid), [
                'json' => [
                    'server_id' => $this->server->uuid,
                    'url' => $targetNode->getConnectionAddress() . '/api/transfers',
                    'token' => 'Bearer ' . $token->toString(),
                    'server' => [
                        'uuid' => $this->server->uuid,
                        'start_on_completion' => false,
                    ],
                ],
            ]);
        } catch (GuzzleException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
