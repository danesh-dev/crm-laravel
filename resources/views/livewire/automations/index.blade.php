<x-ui.page
    :title="__('Automations')"
    :subtitle="__('Coordinate customer targeting, schedules, and email templates.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('automations.create')" icon="plus" wire:navigate>
            {{ __('New Automation') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search automations...') }}"
            class="h-10 w-full rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 sm:max-w-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />
    </div>

    <x-ui.table>
        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Name') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Target') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Schedule') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Template') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($automations as $automation)
                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $automation->name }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ str($automation->target_user_type->value)->headline() }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                        @if($automation->schedule_type === \App\Enums\ScheduleType::AFTER_SIGNUP)
                            {{ trans_choice(':count day after signup|:count days after signup', $automation->schedule?->delay_days ?? 0) }}
                        @else
                            {{ str($automation->schedule_type->value)->headline() }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $automation->emailTemplate?->name ?? '---' }}</td>
                    <td class="px-4 py-3">
                        @if($automation->is_active)
                            <x-ui.badge variant="success">{{ __('Active') }}</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">{{ __('Inactive') }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <x-ui.button :href="route('automations.show', $automation)" variant="secondary" size="sm" icon="eye" wire:navigate>
                                {{ __('Show') }}
                            </x-ui.button>

                            <x-ui.button :href="route('automations.edit', $automation)" variant="secondary" size="sm" icon="pencil-square" wire:navigate>
                                {{ __('Edit') }}
                            </x-ui.button>

                            <x-ui.button
                                variant="ghost"
                                size="sm"
                                wire:click="delete({{ $automation->id }})"
                                wire:confirm="{{ __('Delete this automation?') }}"
                            >
                                {{ __('Delete') }}
                            </x-ui.button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.table-empty colspan="6" :title="__('No automations found.')" :message="__('Create an automation to schedule messages for your customers.')" />
            @endforelse
        </tbody>
    </x-ui.table>

    {{ $automations->links() }}
</x-ui.page>
