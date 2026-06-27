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
            ['label' => __('Total Customers'), 'value' => $stats['overview']['customers'], 'hint' => __('All customer records'), 'variant' => 'neutral'],
            ['label' => __('Active Subscribers'), 'value' => $stats['overview']['active_subscribers'], 'hint' => __('Current paid audience'), 'variant' => 'success'],
            ['label' => __('Active Automations'), 'value' => $stats['overview']['active_automations'], 'hint' => __('Running workflows'), 'variant' => 'info'],
            ['label' => __('Emails Sent Today'), 'value' => $stats['overview']['emails_sent_today'], 'hint' => __('Successful deliveries'), 'variant' => 'success'],
            ['label' => __('Failed Emails Today'), 'value' => $stats['overview']['emails_failed_today'], 'hint' => __('Needs attention'), 'variant' => $stats['overview']['emails_failed_today'] > 0 ? 'danger' : 'neutral'],
            ['label' => __('Failed Jobs Today'), 'value' => $stats['overview']['failed_jobs_today'], 'hint' => __('Queue failures'), 'variant' => $stats['overview']['failed_jobs_today'] > 0 ? 'danger' : 'neutral'],
        ] as $card)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-3">
                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                    <x-ui.badge :variant="$card['variant']">{{ __('Live') }}</x-ui.badge>
                </div>
                <div class="mt-3 text-3xl font-semibold tracking-normal text-zinc-950 dark:text-white">{{ number_format($card['value']) }}</div>
                <div class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $card['hint'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        @foreach([
            [
                'title' => __('Customer Growth'),
                'subtitle' => __('New customer registrations over the last 30 days.'),
                'type' => 'line',
                'chart' => $stats['charts']['customer_growth'],
            ],
            [
                'title' => __('Email Sent vs Failed'),
                'subtitle' => __('Delivery outcomes over the last 30 days.'),
                'type' => 'line',
                'chart' => $stats['charts']['email_sent_failed'],
            ],
            [
                'title' => __('New Subscribers'),
                'subtitle' => __('Subscription starts over the last 30 days.'),
                'type' => 'bar',
                'chart' => $stats['charts']['new_subscribers'],
            ],
            [
                'title' => __('Automation Runs'),
                'subtitle' => __('Automation-created delivery activity over the last 30 days.'),
                'type' => 'bar',
                'chart' => $stats['charts']['automation_runs'],
            ],
        ] as $chart)
            <section class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div>
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ $chart['title'] }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $chart['subtitle'] }}</p>
                </div>

                <div
                    class="js-dashboard-chart mt-4 h-72"
                    data-chart-type="{{ $chart['type'] }}"
                    data-chart-labels='@json($chart['chart']['labels'])'
                    data-chart-series='@json($chart['chart']['datasets'])'
                >
                    <canvas></canvas>
                </div>
            </section>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-4">
        <section class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Top 5 Automations') }}</h2>

            <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($stats['top_automations'] as $automation)
                    <a href="{{ route('automations.show', $automation['id']) }}" wire:navigate class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                        <span class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $automation['name'] }}</span>
                        <x-ui.badge variant="success">{{ number_format($automation['sent_count']) }}</x-ui.badge>
                    </a>
                @empty
                    <div class="py-6 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No sent emails yet.') }}</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Top Templates') }}</h2>

            <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($stats['top_templates'] as $template)
                    <a href="{{ route('templates.show', $template['id']) }}" wire:navigate class="block py-3 first:pt-0 last:pb-0">
                        <div class="flex items-center justify-between gap-3">
                            <span class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $template['name'] }}</span>
                            <x-ui.badge variant="info">{{ number_format($template['sent_count']) }}</x-ui.badge>
                        </div>
                        <div class="mt-1 truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $template['subject'] }}</div>
                    </a>
                @empty
                    <div class="py-6 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No template usage yet.') }}</div>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Queue Health') }}</h2>

            <dl class="mt-4 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending Jobs') }}</dt>
                    <dd class="font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['queue_health']['pending_jobs']) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Reserved Jobs') }}</dt>
                    <dd class="font-semibold text-zinc-950 dark:text-white">{{ number_format($stats['queue_health']['reserved_jobs']) }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Failed Today') }}</dt>
                    <dd>
                        <x-ui.badge :variant="$stats['queue_health']['failed_jobs_today'] > 0 ? 'danger' : 'success'">
                            {{ number_format($stats['queue_health']['failed_jobs_today']) }}
                        </x-ui.badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Oldest Pending') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $stats['queue_health']['oldest_pending_at'] ?? '---' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Latest Customers') }}</h2>

            <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($stats['recent_customers'] as $customer)
                    <a href="{{ route('customers.show', $customer['id']) }}" wire:navigate class="block py-3 first:pt-0 last:pb-0">
                        <div class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $customer['name'] }}</div>
                        <div class="mt-1 truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $customer['email'] }}</div>
                    </a>
                @empty
                    <div class="py-6 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No customers yet.') }}</div>
                @endforelse
            </div>
        </section>
    </div>

    <section>
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Recent Deliveries') }}</h2>
            <x-ui.button :href="route('deliveries.index')" variant="secondary" size="sm" wire:navigate>
                {{ __('View All') }}
            </x-ui.button>
        </div>

        <x-ui.table>
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Customer') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Automation') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Template') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Scheduled') }}</th>
                    <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Sent') }}</th>
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
                                    <!-- {{ __('Show') }} -->
                                </x-ui.button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-ui.table-empty colspan="7" :title="__('No deliveries yet.')" :message="__('Recent automation activity will appear here.')" />
                @endforelse
            </tbody>
        </x-ui.table>
    </section>
</x-ui.page>
