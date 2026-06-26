<?php

namespace RoyalPanel\Services;

use RoyalPanel\Models\EmailTemplate;
use Illuminate\Notifications\Messages\MailMessage;

class EmailTemplateService
{
    public function getTemplate(string $key): ?EmailTemplate
    {
        return EmailTemplate::where('template_key', $key)->where('enabled', true)->first();
    }

    public function applyToMail(?MailMessage $message, string $key, array $replacements = []): ?MailMessage
    {
        if ($message === null) {
            return null;
        }

        $template = $this->getTemplate($key);
        if (!$template) {
            return $message;
        }

        if ($template->subject) {
            $message->subject($this->replacePlaceholders($template->subject, $replacements));
        }

        $message->greeting($this->replacePlaceholders($template->greeting ?: '', $replacements));

        $message->introLines = [];
        $message->outroLines = [];

        if ($template->body) {
            $lines = explode("\n", $template->body);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $message->line($this->replacePlaceholders($line, $replacements));
                }
            }
        }

        if ($template->action_text && $template->action_url) {
            $url = $this->replacePlaceholders($template->action_url, $replacements);
            $message->action($template->action_text, $url);
        }

        if ($template->outro) {
            $lines = explode("\n", $template->outro);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $message->line($this->replacePlaceholders($line, $replacements));
                }
            }
        }

        if ($template->level === 'error') {
            $message->error();
        }

        return $message;
    }

    public function replacePlaceholders(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $text = str_replace('{{' . $key . '}}', (string) $value, $text);
        }
        return $text;
    }
}
