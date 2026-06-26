<x-ui.page
    :title="__('Dashboard')"
    :subtitle="__('A quick read on audience growth, automation health, and email delivery.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('customers.create')" variant="secondary" icon="user-plus" wire:navigate>
            {{ __('New Customer') }}
        </x-ui.button>

        <x-ui.button :href="route('automations.create')" icon="plus" wire:navigate>
            {{ __('New Automation') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['label' => __('Total Customers'), 'value' => number_format($stats['customers']), 'hint' => __('All customer records')],
            ['label' => __('Active Subscribers'), 'value' => number_format($stats['active_subscribers']), 'hint' => __('Current paid audience')],
            ['label' => __('Active Automations'), 'value' => number_format($stats['automations']), 'hint' => __('Running workflows')],
            ['label' => __('Sent Today'), 'value' => number_format($stats['emails_sent_today']), 'hint' => __('Successful deliveries')],
            ['label' => __('Failed Today'), 'value' => number_format($stats['emails_failed_today']), 'hint' => __('Needs attention')],
        ] as $card)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                <div class="mt-3 text-3xl font-semibold tracking-normal text-zinc-950 dark:text-white">{{ $card['value'] }}</div>
                <div class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $card['hint'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <x-ui.table>
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Recent Delivery') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Automation') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Scheduled') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($stats['recent_deliveries'] as $delivery)
                    @php
                        $statusVariant = match ($delivery->status) {
                            \App\Enums\EmailDeliveryStatus::SENT => 'success',
                            \App\Enums\EmailDeliveryStatus::FAILED,
                            \App\Enums\EmailDeliveryStatus::BOUNCED => 'danger',
                            \App\Enums\EmailDeliveryStatus::PROCESSING => 'info',
                            default => 'warning',
                        };
                    @endphp

                    <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                        <td class="px-4 py-3">
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $delivery->customer?->name ?? '---' }}</div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $delivery->emailTemplate?->name ?? '---' }}</div>
                        </td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->automation?->name ?? __('Manual') }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge :variant="$statusVariant">
                                {{ str($delivery->status->value)->headline() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->scheduled_for?->format('Y-m-d H:i') ?? '---' }}</td>
                    </tr>
                @empty
                    <x-ui.table-empty colspan="4" :title="__('No deliveries yet.')" :message="__('Recent automation activity will appear here.')" />
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="space-y-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Today Success Rate') }}</div>
                        <div class="mt-2 text-3xl font-semibold tracking-normal text-zinc-950 dark:text-white">{{ $stats['delivery_success_rate'] }}%</div>
                    </div>

                    <x-ui.badge :variant="$stats['emails_failed_today'] > 0 ? 'danger' : 'success'">
                        {{ trans_choice(':count failed|:count failed', $stats['emails_failed_today']) }}
                    </x-ui.badge>
                </div>

                <div class="mt-4 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="h-full rounded-full bg-zinc-900 dark:bg-white" style="width: {{ $stats['delivery_success_rate'] }}%"></div>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Queue') }}</div>
                <div class="mt-3 text-3xl font-semibold tracking-normal text-zinc-950 dark:text-white">{{ number_format($stats['emails_pending']) }}</div>
                <div class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending email deliveries waiting to be processed.') }}</div>

                <div class="mt-4">
                    <x-ui.button :href="route('deliveries.index')" variant="secondary" class="w-full" wire:navigate>
                        {{ __('View Deliveries') }}
                    </x-ui.button>
                </div>
            </div>
        </div>
    </div>
</x-ui.page>
