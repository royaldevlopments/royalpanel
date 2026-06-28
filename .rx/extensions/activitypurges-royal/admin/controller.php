<?php
namespace RoyalPanel\Http\Controllers\Admin\Extensions\activitypurges;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Support\Facades\DB;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;

class activitypurgesExtensionController extends Controller
{
    public function __construct(
        private ViewFactory $view,
        private BlueprintExtensionLibrary $blueprint,
    ) {}

    /**
     * Handle both GET and POST requests.
     */
    public function index(Request $request): View
    {
        $message = null;

        if ($request->isMethod('post')) {
            // Validate the input.
            // If "quick" is provided, the manual timestamp is not required.
            $validated = $request->validate([
                'tables'    => 'required|array',
                'tables.*'  => 'in:activity_logs,api_logs,audit_logs,failed_jobs',
                'timestamp' => 'required_without:quick|nullable|date',
                'quick'     => 'nullable|in:7,14,30,90,180,365'
            ]);

            // Determine the cutoff timestamp:
            if ($request->has('quick')) {
                $days = (int)$validated['quick'];
                // Use Laravel's now() helper to subtract days.
                $mysqlTimestamp = now()->subDays($days)->format('Y-m-d H:i:s');
            } else {
                $rawTimestamp = $validated['timestamp'];
                $mysqlTimestamp = date('Y-m-d H:i:s', strtotime($rawTimestamp));
            }

            // Map each table to its respective date column.
            $columnsMapping = [
                'activity_logs' => 'timestamp',
                'api_logs'      => 'updated_at',
                'audit_logs'    => 'created_at',
                'failed_jobs'   => 'failed_at',
            ];

            $deletedRecords = [];

            try {
                // Loop through each selected table and delete records older than the computed timestamp.
                foreach ($validated['tables'] as $table) {
                    // Get the date column for this table.
                    $dateColumn = $columnsMapping[$table] ?? 'timestamp';

                    $deleted = DB::table($table)
                        ->where($dateColumn, '<', $mysqlTimestamp)
                        ->delete();
                    $deletedRecords[$table] = $deleted;
                }

                // Prepare a message summarizing the deletion results.
                $msgParts = [];
                foreach ($deletedRecords as $table => $count) {
                    $msgParts[] = "{$table}: {$count} log(s)";
                }
                $message = "Purged records - " . implode(", ", $msgParts);
            } catch (\Exception $e) {
                \Log::error('Purge error: ' . $e->getMessage());
                $message = "An error occurred while purging logs.";
            }
        }

        return $this->view->make('admin.extensions.activitypurges.index', [
            'root'      => "/admin/extensions/activitypurges",
            'blueprint' => $this->blueprint,
            'message'   => $message,
        ]);
    }

    /**
     * Handle POST requests by delegating to index().
     */
    public function post(Request $request): View
    {
        return $this->index($request);
    }
}
