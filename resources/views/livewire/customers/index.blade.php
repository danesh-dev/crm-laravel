<x-ui.page
    :title="__('Customers')"
    :subtitle="__('Manage imported users, subscription state, and audience details.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('customers.create')" icon="plus" wire:navigate>
            {{ __('New Customer') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="grid gap-3 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-900">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search name or email...') }}"
            class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />

        <select wire:model.live="subscription" class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700">
            <option value="">{{ __('All Subscriptions') }}</option>
            <option value="active">{{ __('Active') }}</option>
            <option value="expired">{{ __('Expired') }}</option>
            <option value="none">{{ __('No Subscription') }}</option>
        </select>

        <select wire:model.live="source" class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700">
            <option value="">{{ __('All Sources') }}</option>

            @foreach(\App\Enums\UserSource::cases() as $source)
                <option value="{{ $source->value }}">
                    {{ str($source->value)->headline() }}
                </option>
            @endforeach
        </select>

        <input
            type="number"
            wire:model.live="tag_id"
            placeholder="{{ __('Tag ID') }}"
            class="h-10 rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />
    </div>

    <x-ui.table>
        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Name') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Email') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Phone') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Source') }}</th>
                <!-- TODO: add subscription status field -->
                <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($customers as $customer)
                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $customer->name }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $customer->email }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $customer->phone ?: '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                        {{ $customer->source ? str($customer->source->value)->headline() : '---' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <x-ui.button :href="route('customers.edit', $customer->id)" variant="secondary" size="sm" icon="pencil-square" wire:navigate>
                                {{ __('Edit') }}
                            </x-ui.button>

                            <x-ui.button
                                variant="ghost"
                                size="sm"
                                wire:click="delete({{ $customer->id }})"
                                wire:confirm="{{ __('Are you sure you want to delete this customer?') }}"
                            >
                                {{ __('Delete') }}
                            </x-ui.button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.table-empty colspan="5" :title="__('No customers found.')" :message="__('Try changing the filters or create a new customer.')" />
            @endforelse
        </tbody>
    </x-ui.table>

    {{ $customers->links() }}
</x-ui.page>
