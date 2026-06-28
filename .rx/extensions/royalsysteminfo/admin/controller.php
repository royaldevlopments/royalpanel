<?php

namespace RoyalPanel\Http\Controllers\Admin\Extensions\royalsysteminfo;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\View\Factory as ViewFactory;

class royalsysteminfoExtensionController
{
    public function __construct(
        private ViewFactory $view,
    ) {}

    public function index(Request $request): View
    {
        return $this->view->make('admin.extensions.royalsysteminfo.index', [
            'root' => "/admin/extensions/royalsysteminfo",
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'serverSoftware' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'databaseDriver' => config('database.default'),
            'environment' => app()->environment(),
            'debugMode' => config('app.debug'),
            'cacheDriver' => config('cache.default'),
            'sessionDriver' => config('session.driver'),
            'queueDriver' => config('queue.default'),
            'mailDriver' => config('mail.default'),
            'timezone' => config('app.timezone'),
            'memoryLimit' => ini_get('memory_limit'),
            'maxUploadSize' => ini_get('upload_max_filesize'),
            'maxPostSize' => ini_get('post_max_size'),
            'maxExecTime' => ini_get('max_execution_time'),
            'diskFree' => disk_free_space(base_path()),
            'diskTotal' => disk_total_space(base_path()),
            'extensions' => get_loaded_extensions(),
        ]);
    }
}
