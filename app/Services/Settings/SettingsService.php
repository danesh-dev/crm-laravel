<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SettingsService
{
    private const CACHE_KEY = 'application_settings';

    /** @var array<string, array{group: string, default: mixed, encrypted?: bool}> */
    private const DEFINITIONS = [
        'company_name' => ['group' => 'general', 'default' => 'Customer Management'],
        'sender_name' => ['group' => 'general', 'default' => 'Customer Management'],
        'sender_email' => ['group' => 'general', 'default' => 'hello@example.com'],
        'timezone' => ['group' => 'application', 'default' => 'UTC'],
        'queue_connection' => ['group' => 'queue', 'default' => 'database'],
        'mail_driver' => ['group' => 'mail', 'default' => 'smtp'],
        'mail_host' => ['group' => 'mail', 'default' => 'localhost'],
        'mail_port' => ['group' => 'mail', 'default' => '587'],
        'mail_encryption' => ['group' => 'mail', 'default' => 'tls'],
        'mail_username' => ['group' => 'mail', 'default' => null],
        'mail_password' => ['group' => 'mail', 'default' => null, 'encrypted' => true],
    ];

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (! $this->databaseAvailable()) {
            return $this->defaultValues();
        }

        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $settings = Setting::query()
                ->get()
                ->keyBy('key');

            return collect(self::DEFINITIONS)
                ->mapWithKeys(function (array $definition, string $key) use ($settings): array {
                    $setting = $settings->get($key);

                    if (! $setting) {
                        return [$key => $definition['default']];
                    }

                    return [$key => $this->decodeValue($setting)];
                })
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function update(array $values): void
    {
        foreach (self::DEFINITIONS as $key => $definition) {
            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];
            $encrypted = (bool) ($definition['encrypted'] ?? false);

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'group' => $definition['group'],
                    'value' => $encrypted && filled($value) ? Crypt::encryptString((string) $value) : $value,
                    'is_encrypted' => $encrypted,
                ],
            );
        }

        $this->forget();
        $this->applyToConfig();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function applyToConfig(): void
    {
        $settings = $this->all();

        config([
            'app.name' => $settings['company_name'],
            'app.timezone' => $settings['timezone'],
            'queue.default' => $settings['queue_connection'],
            'mail.default' => $settings['mail_driver'],
            'mail.from.name' => $settings['sender_name'],
            'mail.from.address' => $settings['sender_email'],
            'mail.mailers.smtp.host' => $settings['mail_host'],
            'mail.mailers.smtp.port' => (int) $settings['mail_port'],
            'mail.mailers.smtp.encryption' => blank($settings['mail_encryption']) ? null : $settings['mail_encryption'],
            'mail.mailers.smtp.username' => $settings['mail_username'],
            'mail.mailers.smtp.password' => $settings['mail_password'],
        ]);

        date_default_timezone_set($settings['timezone']);
    }

    private function decodeValue(Setting $setting): mixed
    {
        if (! $setting->is_encrypted || blank($setting->value)) {
            return $setting->value;
        }

        try {
            return Crypt::decryptString($setting->value);
        } catch (Throwable) {
            return null;
        }
    }

    private function databaseAvailable(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultValues(): array
    {
        return collect(self::DEFINITIONS)
            ->mapWithKeys(fn (array $definition, string $key): array => [$key => $definition['default']])
            ->all();
    }
}
