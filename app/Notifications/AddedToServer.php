<?php

namespace RoyalPanel\Notifications;

use RoyalPanel\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AddedToServer extends Notification implements ShouldQueue
{
    use Queueable;

    public object $server;

    public function __construct(array $server)
    {
        $this->server = (object) $server;
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $message = (new MailMessage())
            ->greeting('Hello ' . $this->server->user . '!')
            ->line('You have been added as a subuser for the following server, allowing you certain control over the server.')
            ->line('Server Name: ' . $this->server->name)
            ->action('Visit Server', url('/server/' . $this->server->uuidShort));

        return app(EmailTemplateService::class)->applyToMail($message, 'added_to_server', [
            'name' => $this->server->user,
            'server_name' => $this->server->name,
            'server_url' => url('/server/' . $this->server->uuidShort),
            'app_name' => config('app.name'),
        ]);
    }
}

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->greeting('Hello ' . $this->server->user . '!')
            ->line('You have been added as a subuser for the following server, allowing you certain control over the server.')
            ->line('Server Name: ' . $this->server->name)
            ->action('Visit Server', url('/server/' . $this->server->uuidShort));
    }
}
