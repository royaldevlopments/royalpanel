<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Response;
use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Repositories\Eloquent\ServerRepository;
use Pterodactyl\Services\Servers\ReinstallServerService;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Pterodactyl\Http\Requests\Api\Client\Servers\Settings\RenameServerRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Settings\SetDockerImageRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Settings\ReinstallServerRequest;
use Pterodactyl\Models\ServerDiscordWebhook;
use Pterodactyl\Services\Discord\DiscordWebhookService;

class SettingsController extends ClientApiController
{
    public function __construct(
        private ServerRepository $repository,
        private ReinstallServerService $reinstallServerService,
        private DiscordWebhookService $discord,
    ) {
        parent::__construct();
    }

    public function rename(RenameServerRequest $request, Server $server): JsonResponse
    {
        $name = $request->input('name');
        $description = $request->has('description') ? (string) $request->input('description') : $server->description;
        $this->repository->update($server->id, [
            'name' => $name,
            'description' => $description,
        ]);

        if ($server->name !== $name) {
            Activity::event('server:settings.rename')
                ->property(['old' => $server->name, 'new' => $name])
                ->log();
        }

        if ($server->description !== $description) {
            Activity::event('server:settings.description')
                ->property(['old' => $server->description, 'new' => $description])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function reinstall(ReinstallServerRequest $request, Server $server): JsonResponse
    {
        $this->reinstallServerService->handle($server);

        Activity::event('server:reinstall')->log();

        return new JsonResponse([], Response::HTTP_ACCEPTED);
    }

    public function dockerImage(SetDockerImageRequest $request, Server $server): JsonResponse
    {
        if (!in_array($server->image, array_values($server->egg->docker_images))) {
            throw new BadRequestHttpException('This server\'s Docker image has been manually set by an administrator and cannot be updated.');
        }

        $original = $server->image;
        $server->forceFill(['image' => $request->input('docker_image')])->saveOrFail();

        if ($original !== $server->image) {
            Activity::event('server:startup.image')
                ->property(['old' => $original, 'new' => $request->input('docker_image')])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function maintenance(Server $server): JsonResponse
    {
        $server->forceFill([
            'status' => $server->isMaintenanceMode() ? null : Server::STATUS_MAINTENANCE,
        ])->saveOrFail();

        $status = $server->isMaintenanceMode() ? 'enabled' : 'disabled';

        Activity::event('server:settings.maintenance')->property(['status' => $status])->log();

        $this->discord->send($server, 'maintenance.' . $status, ['status' => $status]);

        return new JsonResponse([
            'success' => true,
            'maintenance' => $server->isMaintenanceMode(),
        ]);
    }

    public function getDiscordWebhook(Server $server): JsonResponse
    {
        $webhook = ServerDiscordWebhook::where('server_id', $server->id)->first();

        return new JsonResponse([
            'url' => $webhook?->url ?? '',
            'events' => $webhook?->events ?? [],
        ]);
    }

    public function setDiscordWebhook(\Illuminate\Http\Request $request, Server $server): JsonResponse
    {
        $request->validate([
            'url' => 'nullable|string|max:255',
            'events' => 'array',
            'events.*' => 'string',
        ]);

        $url = $request->input('url');

        if (empty($url)) {
            ServerDiscordWebhook::where('server_id', $server->id)->delete();

            return new JsonResponse(['success' => true, 'url' => '', 'events' => []]);
        }

        $webhook = ServerDiscordWebhook::updateOrCreate(
            ['server_id' => $server->id],
            ['url' => $url, 'events' => $request->input('events', [])]
        );

        return new JsonResponse([
            'success' => true,
            'url' => $webhook->url,
            'events' => $webhook->events,
        ]);
    }
}
