<?php

namespace RoyalPanel\Notifications;

use RoyalPanel\Models\User;
use RoyalPanel\Services\EmailTemplateService;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MailTested extends Notification
{
    public function __construct(private User $user)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $message = (new MailMessage())
            ->subject('Royal Panel Test Message')
            ->greeting('Hello ' . $this->user->name . '!')
            ->line('This is a test of the Royal Panel mail system. You\'re good to go!');

        return app(EmailTemplateService::class)->applyToMail($message, 'mail_test', [
            'name' => $this->user->name,
            'app_name' => config('app.name'),
        ]);
    }
}
