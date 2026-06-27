<?php

namespace App\Livewire\Settings;

use App\Services\Settings\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Application extends Component
{
    public string $company_name = '';

    public string $sender_name = '';

    public string $sender_email = '';

    public string $timezone = 'UTC';

    public string $queue_connection = 'database';

    public string $mail_driver = 'smtp';

    public ?string $mail_host = null;

    public ?int $mail_port = null;

    public ?string $mail_encryption = null;

    public ?string $mail_username = null;

    public ?string $mail_password = null;

    public function mount(SettingsService $settings): void
    {
        $values = $settings->all();

        $this->company_name = (string) $values['company_name'];
        $this->sender_name = (string) $values['sender_name'];
        $this->sender_email = (string) $values['sender_email'];
        $this->timezone = (string) $values['timezone'];
        $this->queue_connection = (string) $values['queue_connection'];
        $this->mail_driver = (string) $values['mail_driver'];
        $this->mail_host = $values['mail_host'];
        $this->mail_port = (int) $values['mail_port'];
        $this->mail_encryption = $values['mail_encryption'];
        $this->mail_username = $values['mail_username'];
        $this->mail_password = null;
    }

    public function save(SettingsService $settings): void
    {
        $validated = $this->validate($this->rules());

        if (blank($this->mail_password)) {
            unset($validated['mail_password']);
        }

        $settings->update($validated);

        $this->mail_password = null;

        session()->flash('success', __('Settings saved successfully.'));
    }

    public function render(): View
    {
        return view('livewire.settings.application', [
            'timezones' => timezone_identifiers_list(),
            'queueConnections' => ['sync', 'database', 'redis'],
            'mailDrivers' => ['smtp', 'sendmail', 'mailgun', 'ses', 'postmark', 'resend', 'log', 'array'],
            'encryptionOptions' => [
                '' => __('None'),
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ]);
    }

    protected function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_email' => ['required', 'email', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'queue_connection' => ['required', Rule::in(['sync', 'database', 'redis'])],
            'mail_driver' => ['required', Rule::in(['smtp', 'sendmail', 'mailgun', 'ses', 'postmark', 'resend', 'log', 'array'])],
            'mail_host' => ['nullable', 'required_if:mail_driver,smtp', 'string', 'max:255'],
            'mail_port' => ['nullable', 'required_if:mail_driver,smtp', 'integer', 'between:1,65535'],
            'mail_encryption' => ['nullable', Rule::in(['', 'tls', 'ssl'])],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
        ];
    }
}
