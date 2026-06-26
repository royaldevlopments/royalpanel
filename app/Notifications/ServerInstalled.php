<?php

namespace RoyalPanel\Notifications;

use RoyalPanel\Models\User;
use RoyalPanel\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use RoyalPanel\Events\Event;
use RoyalPanel\Models\Server;
use Illuminate\Container\Container;
use RoyalPanel\Events\Server\Installed;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use RoyalPanel\Contracts\Core\ReceivesEvents;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Notifications\Messages\MailMessage;

class ServerInstalled extends Notification implements ShouldQueue, ReceivesEvents
{
    use Queueable;

    public Server $server;

    public User $user;

    public function handle(Event|Installed $event): void
    {
        $event->server->loadMissing('user');

        $this->server = $event->server;
        $this->user = $event->server->user;

        Container::getInstance()->make(Dispatcher::class)->sendNow($this->user, $this);
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $message = (new MailMessage())
            ->greeting('Hello ' . $this->user->username . '.')
            ->line('Your server has finished installing and is now ready for you to use.')
            ->line('Server Name: ' . $this->server->name)
            ->action('Login and Begin Using', route('index'));

        return app(EmailTemplateService::class)->applyToMail($message, 'server_installed', [
            'name' => $this->user->username,
            'username' => $this->user->username,
            'server_name' => $this->server->name,
            'server_url' => route('index'),
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
            ->greeting('Hello ' . $this->user->username . '.')
            ->line('Your server has finished installing and is now ready for you to use.')
            ->line('Server Name: ' . $this->server->name)
            ->action('Login and Begin Using', route('index'));
    }
}
