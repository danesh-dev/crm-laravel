<x-ui.page
    :title="$customer->name"
    :subtitle="$customer->email"
>
    @inject('statusPresenter', 'App\Services\Email\DeliveryStatusPresenter')

    <x-slot:actions>
        <x-ui.button :href="route('customers.index')" variant="secondary" wire:navigate>
            {{ __('Back') }}
        </x-ui.button>

        <x-ui.button :href="route('customers.edit', $customer)" icon="pencil-square" wire:navigate>
            {{ __('Edit Customer') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Overview') }}</h2>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->phone ?: '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Source') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->source ? str($customer->source->value)->headline() : '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Signup Date') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->signup_at?->format('Y-m-d H:i') ?? $customer->created_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Last Login') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $customer->last_login_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Subscription History') }}</h2>

                <div class="mt-5 space-y-3">
                    @forelse($subscriptionHistory as $subscription)
                        @php
                            $subscriptionStatus = $subscription->cancelled_at
                                ? __('Cancelled')
                                : ($subscription->expires_at?->isPast() ? __('Expired') : __('Active'));
                            $subscriptionVariant = $subscription->cancelled_at
                                ? 'danger'
                                : ($subscription->expires_at?->isPast() ? 'warning' : 'success');
                        @endphp

                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="font-medium text-zinc-950 dark:text-white">
                                    {{ $subscription->started_at?->format('Y-m-d H:i') ?? __('Unknown start') }}
                                </div>

                                <x-ui.badge :variant="$subscriptionVariant">{{ $subscriptionStatus }}</x-ui.badge>
                            </div>

                            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                                <div>
                                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Started') }}</dt>
                                    <dd class="mt-1 text-zinc-800 dark:text-zinc-200">{{ $subscription->started_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Expires') }}</dt>
                                    <dd class="mt-1 text-zinc-800 dark:text-zinc-200">{{ $subscription->expires_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Cancelled') }}</dt>
                                    <dd class="mt-1 text-zinc-800 dark:text-zinc-200">{{ $subscription->cancelled_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-zinc-200 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                            {{ __('This customer has never subscribed.') }}
                        </div>
                    @endforelse
                </div>
            </section>

            <section>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Email History') }}</h2>
                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Paginated') }}</span>
                </div>

                <x-ui.table>
                    <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Automation') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Template') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Scheduled') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Sent') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($emailHistory as $delivery)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->automation?->name ?? '---' }}</td>
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
                            <x-ui.table-empty colspan="6" :title="__('No email history.')" :message="__('Email deliveries for this customer will appear here.')" />
                        @endforelse
                    </tbody>
                </x-ui.table>

                <div class="mt-4">
                    {{ $emailHistory->links() }}
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Statistics') }}</h2>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total emails') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ number_format($statistics['total_emails']) }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sent') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-300">{{ number_format($statistics['sent']) }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Failed') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-red-700 dark:text-red-300">{{ number_format($statistics['failed']) }}</div>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pending') }}</div>
                        <div class="mt-2 text-2xl font-semibold text-amber-700 dark:text-amber-300">{{ number_format($statistics['pending']) }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Upcoming Emails') }}</h2>

                <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($upcomingEmails as $delivery)
                        <div class="py-3 first:pt-0 last:pb-0">
                            <div class="font-medium text-zinc-950 dark:text-white">{{ $delivery->automation?->name ?? __('Manual') }}</div>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $delivery->emailTemplate?->name ?? '---' }}</div>
                            <div class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">{{ $delivery->scheduled_for?->format('Y-m-d H:i') ?? '---' }}</div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('No upcoming emails.') }}
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Tags') }}</h2>

                <div class="mt-4 flex flex-wrap gap-2">
                    @forelse($customer->tags as $tag)
                        <x-ui.badge>{{ $tag->name }}</x-ui.badge>
                    @empty
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No tags assigned.') }}</span>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Timeline') }}</h2>

                <div class="mt-5 space-y-4">
                    @forelse($timeline as $item)
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-2.5 w-2.5 rounded-full bg-zinc-900 dark:bg-white"></div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-sm font-medium text-zinc-950 dark:text-white">{{ $item['title'] }}</div>
                                    <x-ui.badge>{{ $item['type'] }}</x-ui.badge>
                                </div>

                                @if($item['description'])
                                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $item['description'] }}</div>
                                @endif

                                <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $item['date']?->format('Y-m-d H:i') ?? '---' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('No timeline activity yet.') }}
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-ui.page>
