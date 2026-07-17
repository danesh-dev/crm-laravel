<?php

namespace App\Services\Settings;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;

class MailSettingsService
{
    public function __construct(
        private SettingsService $settings
    ) {}

    public function applyCached(): void
    {
        $this->apply($this->settings->all());
    }

    public function refresh(): void
    {
        $this->settings->forget();
        $this->apply($this->settings->all());
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function apply(array $settings): void
    {
        $encryption = blank($settings['mail_encryption'] ?? null)
            ? null
            : (string) $settings['mail_encryption'];

        config([
            'mail.default' => (string) $settings['mail_driver'],
            'mail.from.name' => (string) $settings['sender_name'],
            'mail.from.address' => (string) $settings['sender_email'],
            'mail.mailers.smtp.host' => (string) $settings['mail_host'],
            'mail.mailers.smtp.port' => (int) $settings['mail_port'],
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.mailers.smtp.username' => $settings['mail_username'],
            'mail.mailers.smtp.password' => $settings['mail_password'],
        ]);

        $this->forgetMailers();
    }

    public function forgetMailers(): void
    {
        if (app()->resolved('mail.manager') && Mail::getFacadeRoot() instanceof MailFake) {
            return;
        }

        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');

        Facade::clearResolvedInstance('mail.manager');
        Facade::clearResolvedInstance('mailer');
    }
}
