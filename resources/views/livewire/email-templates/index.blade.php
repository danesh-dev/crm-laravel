<x-ui.page
    :title="__('Email Templates')"
    :subtitle="__('Create reusable email content for automations and manual delivery.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('templates.create')" icon="plus" wire:navigate>
            {{ __('New Template') }}
        </x-ui.button>
    </x-slot:actions>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search templates...') }}"
            class="h-10 w-full rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />
    </div>

    <x-ui.table>
        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Name') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Subject') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Created By') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Created At') }}</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($templates as $template)
                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $template->name }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $template->subject }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $template->creator?->name ?? '---' }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $template->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <x-ui.button :href="route('templates.edit', $template)" variant="secondary" size="sm" icon="pencil-square" wire:navigate>
                                {{ __('Edit') }}
                            </x-ui.button>

                            <x-ui.button
                                variant="ghost"
                                size="sm"
                                wire:click="delete({{ $template->id }})"
                                wire:confirm="{{ __('Delete this template?') }}"
                            >
                                {{ __('Delete') }}
                            </x-ui.button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.table-empty colspan="5" :title="__('No templates found.')" :message="__('Create a template to start sending consistent email content.')" />
            @endforelse
        </tbody>
    </x-ui.table>

    {{ $templates->links() }}
</x-ui.page>
