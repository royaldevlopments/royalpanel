<?php

namespace RoyalPanel\Repositories\Wings;

use Webmozart\Assert\Assert;
use RoyalPanel\Models\Server;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\TransferException;
use RoyalPanel\Exceptions\Http\Connection\DaemonConnectionException;

/**
 * @method \RoyalPanel\Repositories\Wings\DaemonPowerRepository setNode(\RoyalPanel\Models\Node $node)
 * @method \RoyalPanel\Repositories\Wings\DaemonPowerRepository setServer(\RoyalPanel\Models\Server $server)
 */
class DaemonPowerRepository extends DaemonRepository
{
    /**
     * Sends a power action to the server instance.
     *
     * @throws DaemonConnectionException
     */
    public function send(string $action): ResponseInterface
    {
        Assert::isInstanceOf($this->server, Server::class);

        try {
            return $this->getHttpClient()->post(
                sprintf('/api/servers/%s/power', $this->server->uuid),
                ['json' => ['action' => $action]]
            );
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
