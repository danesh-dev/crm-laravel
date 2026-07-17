<?php

namespace Tests\Feature\Settings;

use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_mail_settings_override_environment_config(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.from.name' => 'Env Sender',
            'mail.from.address' => 'env@example.com',
            'mail.mailers.smtp.host' => 'env-smtp.example.com',
            'mail.mailers.smtp.port' => 2525,
            'mail.mailers.smtp.scheme' => 'smtp',
            'mail.mailers.smtp.encryption' => null,
            'mail.mailers.smtp.username' => 'env-user',
            'mail.mailers.smtp.password' => 'env-password',
        ]);

        app(SettingsService::class)->update([
            'sender_name' => 'Database Sender',
            'sender_email' => 'database@example.com',
            'mail_driver' => 'smtp',
            'mail_host' => 'db-smtp.example.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'db-user',
            'mail_password' => 'db-password',
        ]);

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('Database Sender', config('mail.from.name'));
        $this->assertSame('database@example.com', config('mail.from.address'));
        $this->assertSame('db-smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('smtp', config('mail.mailers.smtp.scheme'));
        $this->assertSame('tls', config('mail.mailers.smtp.encryption'));
        $this->assertSame('db-user', config('mail.mailers.smtp.username'));
        $this->assertSame('db-password', config('mail.mailers.smtp.password'));
    }

    public function test_changing_database_settings_replaces_resolved_mailer_configuration(): void
    {
        $settings = app(SettingsService::class);

        $settings->update([
            'sender_name' => 'First Sender',
            'sender_email' => 'first@example.com',
            'mail_driver' => 'smtp',
            'mail_host' => 'smtp-one.example.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_username' => 'first-user',
            'mail_password' => 'first-password',
        ]);

        app('mail.manager');
        app('mailer');

        $this->assertTrue(app()->resolved('mail.manager'));
        $this->assertTrue(app()->resolved('mailer'));

        $settings->update([
            'sender_name' => 'Second Sender',
            'sender_email' => 'second@example.com',
            'mail_driver' => 'smtp',
            'mail_host' => 'smtp-two.example.com',
            'mail_port' => 465,
            'mail_encryption' => 'ssl',
            'mail_username' => 'second-user',
            'mail_password' => 'second-password',
        ]);

        $this->assertFalse(app()->resolved('mail.manager'));
        $this->assertFalse(app()->resolved('mailer'));
        $this->assertSame('smtp-two.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('ssl', config('mail.mailers.smtp.encryption'));
        $this->assertSame('second-user', config('mail.mailers.smtp.username'));
        $this->assertSame('second-password', config('mail.mailers.smtp.password'));
    }
}
