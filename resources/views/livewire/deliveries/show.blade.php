<x-ui.page
    :title="__('Delivery #:id', ['id' => $delivery->id])"
    :subtitle="$delivery->customer?->email ?? __('Email delivery details.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('deliveries.index')" variant="secondary" wire:navigate>
            {{ __('Back') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Delivery Details') }}</h2>

                @php
                    $statusVariant = match ($delivery->status) {
                        \App\Enums\EmailDeliveryStatus::SENT => 'success',
                        \App\Enums\EmailDeliveryStatus::FAILED,
                        \App\Enums\EmailDeliveryStatus::BOUNCED => 'danger',
                        \App\Enums\EmailDeliveryStatus::PROCESSING => 'info',
                        default => 'warning',
                    };
                @endphp

                <x-ui.badge :variant="$statusVariant">
                    {{ str($delivery->status->value)->headline() }}
                </x-ui.badge>
            </div>

            <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Customer') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->customer?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Template') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->emailTemplate?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Automation') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->automation?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Provider Message ID') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->provider_message_id ?? '---' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Timing') }}</h2>

            <dl class="mt-5 space-y-4">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Scheduled') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->scheduled_for?->format('Y-m-d H:i') ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Processing') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->processing_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sent') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->sent_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Failed') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $delivery->failed_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    @if($delivery->error_message)
        <div class="rounded-lg border border-red-200 bg-red-50 p-5 text-sm text-red-700 shadow-sm dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
            {{ $delivery->error_message }}
        </div>
    @endif
</x-ui.page>
