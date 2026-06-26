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

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach([
            ['label' => __('Total Customers'), 'value' => $stats['overview']['customers'], 'hint' => __('All customer records')],
            ['label' => __('Active Subscribers'), 'value' => $stats['overview']['active_subscribers'], 'hint' => __('Current paid audience')],
            ['label' => __('Active Automations'), 'value' => $stats['overview']['active_automations'], 'hint' => __('Running workflows')],
            ['label' => __('Emails Sent Today'), 'value' => $stats['overview']['emails_sent_today'], 'hint' => __('Successful deliveries')],
            ['label' => __('Failed Emails Today'), 'value' => $stats['overview']['emails_failed_today'], 'hint' => __('Needs attention')],
            ['label' => __('Pending Queue'), 'value' => $stats['overview']['emails_pending'], 'hint' => __('Waiting to send')],
        ] as $card)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                <div class="mt-3 text-3xl font-semibold tracking-normal text-zinc-950 dark:text-white">{{ number_format($card['value']) }}</div>
                <div class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $card['hint'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Customer Growth') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('New customer registrations over the last 30 days.') }}</p>
                </div>
            </div>

            <div
                class="js-dashboard-chart mt-4 min-h-72"
                data-chart-type="area"
                data-chart-labels='@json($stats['charts']['customer_growth']['labels'])'
                data-chart-series='@json([['name' => __('Customers'), 'data' => $stats['charts']['customer_growth']['series']]])'
            ></div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Email Delivery') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Successfully sent emails over the last 30 days.') }}</p>
                </div>
            </div>

            <div
                class="js-dashboard-chart mt-4 min-h-72"
                data-chart-type="bar"
                data-chart-labels='@json($stats['charts']['email_delivery']['labels'])'
                data-chart-series='@json([['name' => __('Sent Emails'), 'data' => $stats['charts']['email_delivery']['series']]])'
            ></div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <x-ui.table>
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Customer') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Automation') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Template') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Scheduled Time') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Sent Time') }}</th>
                    <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($stats['recent_deliveries'] as $delivery)
                    <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $delivery['customer'] ?? '---' }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery['automation'] ?? __('Manual') }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery['template'] ?? '---' }}</td>
                        <td class="px-4 py-3">
                            <x-ui.badge :variant="$delivery['status_variant']">
                                {{ $delivery['status_label'] }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery['scheduled_for'] ?? '---' }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery['sent_at'] ?? '---' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end">
                                <x-ui.button :href="route('deliveries.show', $delivery['id'])" variant="secondary" size="sm" icon="eye" wire:navigate>
                                    {{ __('Show') }}
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-ui.table-empty colspan="7" :title="__('No deliveries yet.')" :message="__('Recent automation activity will appear here.')" />
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Delivery Health') }}</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Today delivery performance.') }}</p>
                    </div>

                    <x-ui.badge :variant="$stats['delivery_stats']['failed_today'] > 0 ? 'danger' : 'success'">
                        {{ $stats['delivery_stats']['success_rate'] }}%
                    </x-ui.badge>
                </div>

                <div class="mt-5">
                    <div class="flex items-end justify-between">
                        <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Success Rate') }}</span>
                        <span class="text-3xl font-semibold tracking-normal text-zinc-950 dark:text-white">{{ $stats['delivery_stats']['success_rate'] }}%</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $stats['delivery_stats']['success_rate'] }}%"></div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-3 gap-3 text-sm">
                    <div>
                        <div class="font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['delivery_stats']['sent_today']) }}</div>
                        <div class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Sent Today') }}</div>
                    </div>
                    <div>
                        <div class="font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['delivery_stats']['failed_today']) }}</div>
                        <div class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Failed Today') }}</div>
                    </div>
                    <div>
                        <div class="font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['delivery_stats']['pending']) }}</div>
                        <div class="mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div>
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Subscription Distribution') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Current audience subscription state.') }}</p>
                </div>

                <div
                    class="js-dashboard-chart mt-4 min-h-64"
                    data-chart-type="donut"
                    data-chart-labels='@json($stats['subscription_stats']['chart']['labels'])'
                    data-chart-series='@json($stats['subscription_stats']['chart']['series'])'
                ></div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <x-ui.table>
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Email') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Source') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Signup Date') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($stats['recent_customers'] as $customer)
                    <tr
                        class="cursor-pointer hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40"
                        onclick="window.Livewire?.navigate ? Livewire.navigate('{{ route('customers.show', $customer['id']) }}') : window.location.assign('{{ route('customers.show', $customer['id']) }}')"
                    >
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $customer['name'] }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $customer['email'] }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $customer['source'] ? str($customer['source'])->headline() : '---' }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $customer['signup_date'] ?? '---' }}</td>
                    </tr>
                @empty
                    <x-ui.table-empty colspan="4" :title="__('No customers yet.')" :message="__('New customers will appear here.')" />
                @endforelse
            </tbody>
        </x-ui.table>

        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Active Automations') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Currently running workflows.') }}</p>
                </div>
            </div>

            <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($stats['active_automations'] as $automation)
                    <div class="py-4 first:pt-0 last:pb-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate font-medium text-zinc-950 dark:text-white">{{ $automation['name'] }}</div>
                                <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $automation['email_template'] ?? '---' }}</div>
                            </div>

                            <x-ui.badge variant="success">{{ __('Active') }}</x-ui.badge>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Schedule') }}</div>
                                <div class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ $automation['schedule'] }}</div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Target User Type') }}</div>
                                <div class="mt-1 font-medium text-zinc-800 dark:text-zinc-200">{{ $automation['target_user_type'] }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No active automations yet.') }}
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                <x-ui.button :href="route('automations.index')" variant="secondary" class="w-full" wire:navigate>
                    {{ __('View All Automations') }}
                </x-ui.button>
            </div>
        </div>
    </div>
</x-ui.page>
