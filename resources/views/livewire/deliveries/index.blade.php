<x-ui.page
    :title="__('Deliveries')"
    :subtitle="__('Track scheduled, sent, and failed email deliveries.')"
>
    <div class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm sm:grid-cols-2 dark:border-zinc-700 dark:bg-zinc-900">
        <input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search customer...') }}"
            class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />

        <select wire:model.live="status" class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700">
            <option value="">{{ __('All Statuses') }}</option>

            @foreach(\App\Enums\EmailDeliveryStatus::cases() as $status)
                <option value="{{ $status->value }}">
                    {{ str($status->value)->headline() }}
                </option>
            @endforeach
        </select>
    </div>

    <x-ui.table>
        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Customer') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Template') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Automation') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Scheduled') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Sent') }}</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($deliveries as $delivery)
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
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $delivery->customer?->email ?? '---' }}</div>
                    </td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->emailTemplate?->name ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->automation?->name ?? '---' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :variant="$statusVariant">
                            {{ str($delivery->status->value)->headline() }}
                        </x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->scheduled_for?->format('Y-m-d H:i') ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $delivery->sent_at?->format('Y-m-d H:i') ?? '---' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end">
                            @if($delivery->status === \App\Enums\EmailDeliveryStatus::FAILED)
                                <x-ui.button
                                    variant="secondary"
                                    size="sm"
                                    wire:click="retry({{ $delivery->id }})"
                                >
                                    {{ __('Retry') }}
                                </x-ui.button>
                            @else
                                <span class="text-sm text-zinc-400 dark:text-zinc-500">{{ __('No action') }}</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.table-empty colspan="7" :title="__('No deliveries found.')" :message="__('Scheduled emails will appear here when automations run.')" />
            @endforelse
        </tbody>
    </x-ui.table>

    {{ $deliveries->links() }}
</x-ui.page>
