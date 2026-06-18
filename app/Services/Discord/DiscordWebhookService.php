<?php

namespace RoyalPanel\Services\Discord;

use Illuminate\Support\Facades\Http;
use RoyalPanel\Models\Server;
use RoyalPanel\Models\ServerDiscordWebhook;

class DiscordWebhookService
{
    public function send(Server $server, string $event, array $data = []): void
    {
        $webhook = ServerDiscordWebhook::where('server_id', $server->id)->first();

        if (!$webhook || !$webhook->url) {
            return;
        }

        $events = $webhook->events ?? [];
        if (!in_array($event, $events) && !in_array('*', $events)) {
            return;
        }

        $embeds = [
            [
                'title' => $this->getEventTitle($event),
                'description' => $this->getEventDescription($event, $server, $data),
                'color' => $this->getEventColor($event),
                'timestamp' => now()->toIso8601String(),
                'footer' => [
                    'text' => $server->name . ' (' . $server->uuidShort . ')',
                ],
            ],
        ];

        Http::post($webhook->url, [
            'embeds' => $embeds,
        ]);
    }

    private function getEventTitle(string $event): string
    {
        switch ($event) {
            case 'power.start': return 'Server Started';
            case 'power.stop': return 'Server Stopped';
            case 'power.kill': return 'Server Killed';
            case 'power.restart': return 'Server Restarted';
            case 'crash': return 'Server Crashed';
            case 'install': return 'Server Installed';
            case 'reinstall': return 'Server Reinstalled';
            case 'backup.complete': return 'Backup Completed';
            case 'backup.failed': return 'Backup Failed';
            case 'resource.limit': return 'Resource Limit Reached';
            case 'maintenance.on': return 'Maintenance Mode Enabled';
            case 'maintenance.off': return 'Maintenance Mode Disabled';
            default: return 'Server Event: ' . $event;
        }
    }

    private function getEventDescription(string $event, Server $server, array $data): string
    {
        switch ($event) {
            case 'power.start': return "**{$server->name}** has been started.";
            case 'power.stop': return "**{$server->name}** has been stopped.";
            case 'power.kill': return "**{$server->name}** has been killed.";
            case 'power.restart': return "**{$server->name}** has been restarted.";
            case 'crash': return "**{$server->name}** has crashed.\n" . ($data['reason'] ?? 'No reason provided.');
            case 'install': return "**{$server->name}** has been installed.";
            case 'reinstall': return "**{$server->name}** is being reinstalled.";
            case 'backup.complete':
                $backupName = $data['backup_name'] ?? 'unknown';
                return "Backup **{$backupName}** for **{$server->name}** completed.";
            case 'backup.failed':
                $backupName = $data['backup_name'] ?? 'unknown';
                return "Backup **{$backupName}** for **{$server->name}** failed.";
            case 'resource.limit':
                $resource = $data['resource'] ?? 'unknown';
                return "**{$server->name}** has reached its {$resource} limit.";
            case 'maintenance.on': return "**{$server->name}** is now in maintenance mode.";
            case 'maintenance.off': return "**{$server->name}** is no longer in maintenance mode.";
            default: return "Event **{$event}** triggered for **{$server->name}**.";
        }
    }

    private function getEventColor(string $event): int
    {
        $green = str_contains($event, 'start') || str_contains($event, 'complete') || str_contains($event, 'install') || str_contains($event, 'off');
        $red = str_contains($event, 'stop') || str_contains($event, 'kill') || str_contains($event, 'crash') || str_contains($event, 'failed') || str_contains($event, 'limit') || (str_contains($event, 'on') && !str_contains($event, 'start'));

        if ($green) return 0x00ff00;
        if ($red) return 0xff0000;
        return 0x3498db;
    }
}
