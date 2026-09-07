<x-ui.page
    :title="$automation->name"
    :subtitle="$automation->description ?: __('Automation details, delivery history, and controls.')"
>
    @inject('automationDisplay', 'App\Services\Automation\AutomationDisplayService')
    @inject('statusPresenter', 'App\Services\Email\DeliveryStatusPresenter')

    <x-slot:actions>
        <div class="flex flex-wrap justify-end gap-2">
            <x-ui.button :href="route('automations.index')" variant="secondary" wire:navigate>
                {{ __('Back') }}
            </x-ui.button>

            <x-ui.button :href="route('automations.edit', $automation)" icon="pencil-square" wire:navigate>
                {{ __('Edit') }}
            </x-ui.button>
        </div>
    </x-slot:actions>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Overview') }}</h2>

                    <x-ui.badge :variant="$automation->is_active ? 'success' : 'neutral'">
                        {{ $automation->is_active ? __('Active') : __('Inactive') }}
                    </x-ui.badge>
                </div>

                <dl class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Active') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->is_active ? __('Yes') : __('No') }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Description') }}</dt>
                        <dd class="mt-1 text-zinc-700 dark:text-zinc-300">{{ $automation->description ?: '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Target Users') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automationDisplay->audience($automation) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Required Tags') }}</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($automation->tags as $tag)
                                    <x-ui.badge>{{ $tag->name }}</x-ui.badge>
                                @empty
                                    <span class="font-medium text-zinc-950 dark:text-white">{{ __('Any tags') }}</span>
                                @endforelse
                            </div>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Schedule') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automationDisplay->schedule($automation) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Repeat Count') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automationDisplay->repeat($automation) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email Template') }}</dt>
                        <dd class="mt-1 flex flex-wrap items-center gap-2 font-medium text-zinc-950 dark:text-white">
                            <span>{{ $automation->emailTemplate?->name ?? '---' }}</span>

                            @if($automation->emailTemplate)
                                <x-ui.button :href="route('templates.show', $automation->emailTemplate)" variant="secondary" size="sm" icon="eye" wire:navigate>
                                    <!-- {{ __('Show Template') }} -->
                                </x-ui.button>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Delivery Mode') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ __('Bulk BCC') }}</dd>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Daily limit: :limit · Batch size: :size', ['limit' => config('mail.automation.daily_limit'), 'size' => config('mail.automation.bcc_batch_size')]) }}</p>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Statistics') }}</h2>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total customers matching') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($statistics['customers_matching']) }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total queued') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($statistics['total_queued']) }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total sent') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($statistics['total_sent']) }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Failed') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-red-700 dark:text-red-300">{{ number_format($statistics['failed']) }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-amber-700 dark:text-amber-300">{{ number_format($statistics['pending']) }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Success rate') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ $statistics['success_rate'] }}%</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Last execution') }}</div>
                        <div class="mt-2 text-base font-semibold text-zinc-950 dark:text-white">{{ $statistics['last_execution'] ?? '---' }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Next execution') }}</div>
                        <div class="mt-2 text-base font-semibold text-zinc-950 dark:text-white">{{ $statistics['next_execution'] ?? '---' }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div>
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Daily Delivery Activity') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Queued, sent, and failed deliveries over the last 30 days.') }}</p>
                </div>

                <div
                    class="js-dashboard-chart mt-4 h-72"
                    data-chart-type="area"
                    data-chart-labels='@json($dailyChart['labels'])'
                    data-chart-series='@json($dailyChart['series'])'
                >
                    <canvas></canvas>
                </div>
            </section>

            <section>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Recent Deliveries') }}</h2>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Last 20') }}</span>
                </div>

                <x-ui.table>
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Customer') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Template') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Scheduled') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Sent') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($recentDeliveries as $delivery)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $delivery->customer?->name ?? '---' }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $delivery->customer?->email ?? '---' }}</div>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->emailTemplate?->name ?? '---' }}</td>
                                <td class="px-4 py-3">
                                    <x-ui.badge :variant="$statusPresenter->badgeVariant($delivery->status)">
                                        {{ str($delivery->status->value)->headline() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->scheduled_for?->format('Y-m-d H:i') ?? '---' }}</td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->sent_at?->format('Y-m-d H:i') ?? '---' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end">
                                        <x-ui.button :href="route('deliveries.show', $delivery)" variant="secondary" size="sm" icon="eye" wire:navigate>
                                            <!-- {{ __('View') }} -->
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-ui.table-empty colspan="6" :title="__('No deliveries yet.')" :message="__('Delivery activity for this automation will appear here.')" />
                        @endforelse
                    </tbody>
                </x-ui.table>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Schedule Card') }}</h2>

                <div class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ str($automation->schedule_type->value)->headline() }}</div>
                    <div class="mt-2 text-lg font-semibold text-zinc-950 dark:text-white">{{ $automationDisplay->scheduleCard($automation) }}</div>
                </div>

                <dl class="mt-5 space-y-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Run Date') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->schedule?->run_date?->format('Y-m-d') ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Delay Days') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->schedule?->delay_days ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Weekday') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automationDisplay->weekdayName($automation->schedule?->weekday) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Month Day') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automation->schedule?->month_day ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Send Time') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $automationDisplay->sendTime($automation) }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Actions') }}</h2>

                <div class="mt-5 grid gap-2">
                    <x-ui.button :href="route('automations.edit', $automation)" variant="secondary" icon="pencil-square" wire:navigate>
                        {{ __('Edit') }}
                    </x-ui.button>

                    <x-ui.button variant="secondary" icon="play" wire:click="runNow">
                        {{ __('Run Now') }}
                    </x-ui.button>

                    <x-ui.button variant="secondary" icon="document-duplicate" wire:click="duplicate">
                        {{ __('Duplicate') }}
                    </x-ui.button>

                    @if($automation->is_active)
                        <x-ui.button variant="secondary" icon="pause" wire:click="pause">
                            {{ __('Pause') }}
                        </x-ui.button>
                    @else
                        <x-ui.button variant="secondary" icon="play" wire:click="resume">
                            {{ __('Resume') }}
                        </x-ui.button>
                    @endif

                    <x-ui.button
                        variant="danger"
                        icon="trash"
                        wire:click="delete"
                        wire:confirm="{{ __('Delete this automation?') }}"
                    >
                        {{ __('Delete') }}
                    </x-ui.button>
                </div>
            </section>
        </div>
    </div>
</x-ui.page>
