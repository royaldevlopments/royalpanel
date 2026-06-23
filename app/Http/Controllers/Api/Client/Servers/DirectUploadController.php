<?php

namespace RoyalPanel\Http\Controllers\Api\Client\Servers;

use RoyalPanel\Models\Server;
use Illuminate\Http\JsonResponse;
use RoyalPanel\Http\Controllers\Api\Client\ClientApiController;
use RoyalPanel\Http\Requests\Api\Client\Servers\Files\DirectUploadRequest;

class DirectUploadController extends ClientApiController
{
    public function __invoke(DirectUploadRequest $request, Server $server): JsonResponse
    {
        $request->validate([
            'files' => 'required|file',
            'directory' => 'nullable|string',
        ]);

        $file = $request->file('files');
        $directory = $request->input('directory', '/');

        $basePath = sprintf('%s/%s', rtrim($server->node->daemonBase, '/'), $server->uuid);
        $targetDir = $basePath . '/' . ltrim($directory, '/');

        $resolvedBase = realpath($basePath);
        $resolvedTarget = realpath($targetDir) ?: $targetDir;

        if ($resolvedBase === false || !str_starts_with($resolvedTarget, $resolvedBase)) {
            return new JsonResponse(['error' => 'Invalid directory path'], 400);
        }

        if (!is_dir($resolvedTarget)) {
            @mkdir($resolvedTarget, 0755, true);
        }

        $filename = $file->getClientOriginalName();

        $file->move($resolvedTarget, $filename);

        return new JsonResponse(['success' => true, 'filename' => $filename]);
    }
}
