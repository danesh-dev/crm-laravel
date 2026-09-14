<x-ui.page
    :title="__('Deliveries')"
    :subtitle="__('Track scheduled, processing, sent, failed, and cancelled email deliveries.')"
>
    @inject('statusPresenter', 'App\Services\Email\DeliveryStatusPresenter')

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if($message)
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
            {{ $message }}
        </div>
    @endif

    <div class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-7 dark:border-zinc-700 dark:bg-zinc-900">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search customer name or email...') }}"
            class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 xl:col-span-2 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />

        <select wire:model.live="status" class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700">
            <option value="">{{ __('All Statuses') }}</option>

            @foreach(\App\Enums\EmailDeliveryStatus::cases() as $status)
                <option value="{{ $status->value }}">
                    {{ str($status->value)->headline() }}
                </option>
            @endforeach
        </select>

        <select wire:model.live="automation_id" class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700">
            <option value="">{{ __('All Automations') }}</option>

            @foreach($automations as $automation)
                <option value="{{ $automation->id }}">{{ $automation->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="email_template_id" class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700">
            <option value="">{{ __('All Templates') }}</option>

            @foreach($emailTemplates as $template)
                <option value="{{ $template->id }}">{{ $template->name }}</option>
            @endforeach
        </select>

        <input
            type="date"
            wire:model.live="date_from"
            aria-label="{{ __('Date from') }}"
            class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />

        <input
            type="date"
            wire:model.live="date_to"
            aria-label="{{ __('Date to') }}"
            class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />

        <x-ui.button
            variant="secondary"
            icon="arrow-path"
            wire:click="retryFilteredFailures"
            wire:confirm="{{ __('Retry all :count failed deliveries matching the current filters?', ['count' => $failedDeliveryCount]) }}"
            wire:loading.attr="disabled"
            wire:target="retryFilteredFailures"
            :disabled="$failedDeliveryCount === 0"
        >
            <span wire:loading.remove wire:target="retryFilteredFailures">
                {{ __('Retry Failed (:count)', ['count' => $failedDeliveryCount]) }}
            </span>
            <span wire:loading wire:target="retryFilteredFailures">{{ __('Retrying...') }}</span>
        </x-ui.button>
    </div>

    <div wire:loading.delay class="text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('Loading deliveries...') }}
    </div>

    <x-ui.table>
        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Customer') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Email') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Automation') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Template') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Batch') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Scheduled At') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Processing At') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Sent At') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Failed At') }}</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($deliveries as $delivery)
                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $delivery->customer?->name ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->customer?->email ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->automation?->name ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->emailTemplate?->name ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                        @if($delivery->batch)
                            #{{ $delivery->batch->id }} · {{ trans_choice(':count recipient|:count recipients', $delivery->batch->recipient_count) }}
                        @else
                            ---
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :variant="$statusPresenter->badgeVariant($delivery->status)">
                            {{ str($delivery->status->value)->headline() }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->scheduled_for?->format('Y-m-d H:i') ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->processing_at?->format('Y-m-d H:i') ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->sent_at?->format('Y-m-d H:i') ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->failed_at?->format('Y-m-d H:i') ?? '---' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <x-ui.button :href="route('deliveries.show', $delivery)" variant="secondary" size="sm" icon="eye" wire:navigate>
                                <!-- {{ __('View') }} -->
                            </x-ui.button>

                            @if($delivery->status === \App\Enums\EmailDeliveryStatus::FAILED)
                                <x-ui.button
                                    variant="secondary"
                                    size="sm"
                                    icon="arrow-path"
                                    wire:click="retry({{ $delivery->id }})"
                                >
                                    <!-- {{ __('Retry') }} -->
                                </x-ui.button>
                            @endif

                            @if($delivery->status === \App\Enums\EmailDeliveryStatus::PENDING)
                                <x-ui.button
                                    variant="danger"
                                    size="sm"
                                    icon="x-mark"
                                    wire:click="cancel({{ $delivery->id }})"
                                    wire:confirm="{{ __('Cancel this pending delivery?') }}"
                                >
                                    <!-- {{ __('Cancel') }} -->
                                </x-ui.button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.table-empty colspan="11" :title="__('No deliveries found.')" :message="__('Try changing the filters or wait for automations to schedule email deliveries.')" />
            @endforelse
        </tbody>
    </x-ui.table>

    {{ $deliveries->links() }}
</x-ui.page>
