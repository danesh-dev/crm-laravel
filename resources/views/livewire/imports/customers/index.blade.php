<x-ui.page
    :title="__('Import Customers')"
    :subtitle="__('Upload a JSON file with customers and subscription history.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('customers.index')" variant="secondary" wire:navigate>
            {{ __('Back to Customers') }}
        </x-ui.button>
    </x-slot:actions>

    @if($importRun && in_array($importRun->status, ['pending', 'processing'], true))
        <div wire:poll.2s></div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Upload JSON') }}</h2>

                <form wire:submit="import" class="mt-5 space-y-5">
                    <flux:input wire:model="file" type="file" :label="__('Customer JSON File')" accept="application/json,.json" required />

                    @error('file') <div class="text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror

                    <div wire:loading wire:target="file" class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Uploading file...') }}
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="import,file">
                            {{ __('Import Customers') }}
                        </x-ui.button>

                        <span wire:loading wire:target="import" class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Preparing import...') }}
                        </span>
                    </div>
                </form>
            </section>

            @if($importRun)
                <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Import Report') }}</h2>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $importRun->original_filename ?? __('Customer import') }}</p>
                        </div>

                        <x-ui.badge :variant="match ($importRun->status) {
                            'finished' => 'success',
                            'failed' => 'danger',
                            'processing' => 'info',
                            default => 'warning',
                        }">
                            {{ str($importRun->status)->headline() }}
                        </x-ui.badge>
                    </div>

                    @php
                        $processed = $importRun->processed_records;
                        $total = $importRun->total_records ?: $processed;
                        $progress = $total > 0 ? min(100, (int) round(($processed / $total) * 100)) : 0;
                    @endphp

                    <div class="mt-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ __('Progress') }}</span>
                            <span class="text-zinc-500 dark:text-zinc-400">{{ $progress }}%</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full bg-zinc-900 dark:bg-white" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <dl class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach([
                            __('Customers Created') => $importRun->customers_created,
                            __('Customers Updated') => $importRun->customers_updated,
                            __('Subscriptions Created') => $importRun->subscriptions_created,
                            __('Duplicate Subscriptions Skipped') => $importRun->duplicate_subscriptions_skipped,
                            __('Invalid Rows') => $importRun->invalid_rows,
                            __('Processing Time') => $importRun->processing_time_ms !== null ? number_format($importRun->processing_time_ms / 1000, 2).'s' : '---',
                        ] as $label => $value)
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                                <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</dt>
                                <dd class="mt-2 text-2xl font-semibold text-zinc-950 dark:text-white">{{ is_numeric($value) ? number_format($value) : $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section>
                    <div class="mb-3">
                        <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Invalid Rows') }}</h2>
                    </div>

                    <x-ui.table>
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Row') }}</th>
                                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Error') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($importRun->errors ?? [] as $error)
                                <tr>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $error['row'] }}</td>
                                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $error['message'] }}</td>
                                </tr>
                            @empty
                                <x-ui.table-empty colspan="2" :title="__('No invalid rows.')" :message="__('Validation errors will appear here.')" />
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Recent Imports') }}</h2>

                <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse($recentImports as $recentImport)
                        <button type="button" wire:click="$set('importRunId', {{ $recentImport->id }})" class="block w-full py-3 text-left first:pt-0 last:pb-0">
                            <div class="flex items-center justify-between gap-3">
                                <span class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $recentImport->original_filename ?? __('Customer import') }}</span>
                                <x-ui.badge>{{ str($recentImport->status)->headline() }}</x-ui.badge>
                            </div>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $recentImport->created_at?->format('Y-m-d H:i') }}</div>
                        </button>
                    @empty
                        <div class="py-6 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No imports yet.') }}</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</x-ui.page>
