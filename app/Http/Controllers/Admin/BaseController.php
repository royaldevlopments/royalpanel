<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Pterodactyl\Models\ActivityLog;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Security\IpGeoService;
use Pterodactyl\Services\Helpers\SoftwareVersionService;

class BaseController extends Controller
{
    /**
     * BaseController constructor.
     */
    public function __construct(
        private SoftwareVersionService $version,
        private IpGeoService $geo,
    ) {
    }

    /**
     * Return the admin index view.
     */
    public function index(): View
    {
        return view('admin.index', [
            'version' => $this->version,
            'topCountries' => $this->geo->batchTopCountries(10),
            'recentLogs' => ActivityLog::query()
                ->with('actor')
                ->latest('timestamp')
                ->limit(10)
                ->get(),
        ]);
    }
}
