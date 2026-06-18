<?php

namespace RoyalPanel\Http\Controllers\Admin;

use Illuminate\View\View;
use RoyalPanel\Models\ActivityLog;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Services\Security\IpGeoService;
use RoyalPanel\Services\Helpers\SoftwareVersionService;

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
