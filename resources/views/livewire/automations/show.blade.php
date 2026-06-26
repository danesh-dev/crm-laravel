<x-ui.page
    :title="$automation->name"
    :subtitle="$automation->description ?: __('Automation details and schedule settings.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('automations.index')" variant="secondary" wire:navigate>
            {{ __('Back') }}
        </x-ui.button>

        <x-ui.button :href="route('automations.edit', $automation)" icon="pencil-square" wire:navigate>
            {{ __('Edit Automation') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Automation Details') }}</h2>

                <x-ui.badge :variant="$automation->is_active ? 'success' : 'neutral'">
                    {{ $automation->is_active ? __('Active') : __('Inactive') }}
                </x-ui.badge>
            </div>

            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Target User Type') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ str($automation->target_user_type->value)->headline() }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Schedule Type') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ str($automation->schedule_type->value)->headline() }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email Template') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->emailTemplate?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Created By') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->creator?->name ?? '---' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Schedule') }}</h2>

            <dl class="mt-5 space-y-4">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Delay Days') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->schedule?->delay_days ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Weekday') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->schedule?->weekday ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Month Day') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->schedule?->month_day ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Send Time') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->schedule?->send_time?->format('H:i') ?? '---' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-ui.page>
