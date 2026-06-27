<x-ui.page
    :title="__('Application Settings')"
    :subtitle="__('Manage database-backed application, mail, and queue settings without editing environment files.')"
>
    <form wire:submit="save" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('General') }}</h2>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <flux:input wire:model="company_name" :label="__('Company Name')" required />
                    <flux:input wire:model="sender_name" :label="__('Sender Name')" required />
                    <flux:input wire:model="sender_email" :label="__('Sender Email')" type="email" required class="md:col-span-2" />
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Mail') }}</h2>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <flux:select wire:model.live="mail_driver" :label="__('Mail Driver')" required>
                        @foreach($mailDrivers as $driver)
                            <flux:select.option value="{{ $driver }}">{{ str($driver)->headline() }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="mail_encryption" :label="__('Encryption')">
                        @foreach($encryptionOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="mail_host" :label="__('Mail Host')" />
                    <flux:input wire:model="mail_port" :label="__('Mail Port')" type="number" min="1" max="65535" />
                    <flux:input wire:model="mail_username" :label="__('Username')" autocomplete="off" />
                    <flux:input wire:model="mail_password" :label="__('Password')" type="password" autocomplete="new-password" viewable />
                </div>
            </section>

            <!-- TODO:remove -->
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Queue') }}</h2>

                <div class="mt-5 max-w-sm">
                    <flux:select wire:model="queue_connection" :label="__('Queue Connection')" required>
                        @foreach($queueConnections as $connection)
                            <flux:select.option value="{{ $connection }}">{{ str($connection)->headline() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Application') }}</h2>

                <div class="mt-5 max-w-sm">
                    <flux:select wire:model="timezone" :label="__('Timezone')" required searchable>
                        @foreach($timezones as $timezoneOption)
                            <flux:select.option value="{{ $timezoneOption }}">{{ $timezoneOption }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <div class="sticky top-6 rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Summary') }}</h2>

                <dl class="mt-5 space-y-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Company') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $company_name ?: '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sender') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $sender_name ?: '---' }}</dd>
                        <dd class="text-sm text-zinc-500 dark:text-zinc-400">{{ $sender_email ?: '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Mail') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ str($mail_driver)->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Queue') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ str($queue_connection)->headline() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Timezone') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $timezone }}</dd>
                    </div>
                </dl>

                @if(session('success'))
                    <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-6">
                    <x-ui.button type="submit" class="w-full">
                        {{ __('Save Settings') }}
                    </x-ui.button>
                </div>
            </div>
        </aside>
    </form>
</x-ui.page>
