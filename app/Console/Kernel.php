<?php

namespace RoyalPanel\Console;

use Ramsey\Uuid\Uuid;
use RoyalPanel\Models\ActivityLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand;
use RoyalPanel\Repositories\Eloquent\SettingsRepository;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use RoyalPanel\Services\Telemetry\TelemetryCollectionService;
use RoyalPanel\Console\Commands\Schedule\ProcessRunnableCommand;
use RoyalPanel\Console\Commands\Maintenance\PruneOrphanedBackupsCommand;
use RoyalPanel\Console\Commands\Maintenance\CleanServiceBackupFilesCommand;

// Import Blueprint schedules, telemetry and library
use RoyalPanel\Services\Telemetry\RegisterBlueprintTelemetry;
use RoyalPanel\BlueprintFramework\GetExtensionSchedules;
use RoyalPanel\BlueprintFramework\Libraries\ExtensionLibrary\Console\BlueprintConsoleLibrary as BlueprintExtensionLibrary;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('bp:telemetry')->hourly();

        $schedule->command('bp:version:cache')->dailyAt('04:00');

        $schedule->command('bp:cache')->everyMinute();
        // https://laravel.com/docs/10.x/upgrade#redis-cache-tags
        $schedule->command('cache:prune-stale-tags')->hourly();

        // Execute scheduled commands for servers every minute, as if there was a normal cron running.
        $schedule->command(ProcessRunnableCommand::class)->everyMinute()->withoutOverlapping();
        $schedule->command(CleanServiceBackupFilesCommand::class)->daily();

        if (config('backups.prune_age')) {
            // Every 30 minutes, run the backup pruning command so that any abandoned backups can be deleted.
            $schedule->command(PruneOrphanedBackupsCommand::class)->everyThirtyMinutes();
        }

        if (config('activity.prune_days')) {
            $schedule->command(PruneCommand::class, ['--model' => [ActivityLog::class]])->daily();
        }

        // RoyalPanel telemetry
        if (config('royalpanel.telemetry.enabled')) {
            $this->registerTelemetry($schedule);
        }

        // ============================
        //    BLUEPRINT SCHEDULES
        // ============================

        // Blueprint telemetry
        $blueprint = app()->make(BlueprintExtensionLibrary::class);
        if ($blueprint->dbGet('blueprint', 'flags:telemetry_enabled', 0)) {
            $registerBlueprintTelemetry = app()->make(RegisterBlueprintTelemetry::class);
            $registerBlueprintTelemetry->register($schedule);
        }

        // Blueprint-related utilities
        $randTime = str_pad(rand(0, 23), 2, '0', STR_PAD_LEFT) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        $schedule->command('bp:version:cache')->dailyAt($randTime);
        $schedule->command('bp:meta')->dailyAt($randTime);

        // Blueprint extension schedules
        GetExtensionSchedules::schedules($schedule);
    }

    /**
     * I wonder what this does.
     *
     * @throws \RoyalPanel\Exceptions\Model\DataValidationException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerTelemetry(Schedule $schedule): void
    {
        $settingsRepository = app()->make(SettingsRepository::class);

        $uuid = $settingsRepository->get('app:telemetry:uuid');
        if (is_null($uuid)) {
            $uuid = Uuid::uuid4()->toString();
            $settingsRepository->set('app:telemetry:uuid', $uuid);
        }

        // Calculate a fixed time to run the data push at, this will be the same time every day.
        $time = hexdec(str_replace('-', '', substr($uuid, 27))) % 1440;
        $hour = floor($time / 60);
        $minute = $time % 60;

        // Run the telemetry collector.
        $schedule->call(app()->make(TelemetryCollectionService::class))->description('Collect Telemetry')->dailyAt("$hour:$minute");
    }
}
