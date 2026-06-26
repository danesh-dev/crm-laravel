<x-ui.page
    :title="__('Tags')"
    :subtitle="__('Organize customers with reusable audience labels.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('tags.create')" icon="plus" wire:navigate>
            {{ __('New Tag') }}
        </x-ui.button>
    </x-slot:actions>

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

    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search tags...') }}"
            class="h-10 w-full rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 sm:max-w-sm dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
        />
    </div>

    <div wire:loading.delay class="text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('Loading tags...') }}
    </div>

    <x-ui.table>
        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
            <tr>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Name') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Slug') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Customers') }}</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-600 dark:text-zinc-300">{{ __('Created At') }}</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-600 dark:text-zinc-300">{{ __('Actions') }}</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse($tags as $tag)
                <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $tag->name }}</td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $tag->slug }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge>{{ number_format($tag->customers_count) }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $tag->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <x-ui.button :href="route('tags.edit', $tag)" variant="secondary" size="sm" icon="pencil-square" wire:navigate>
                                <!-- {{ __('Edit') }} -->
                            </x-ui.button>

                            <x-ui.button
                                variant="danger"
                                size="sm"
                                icon="trash"
                                wire:click="delete({{ $tag->id }})"
                                wire:confirm="{{ __('Delete this tag?') }}"
                            >
                                <!-- {{ __('Delete') }} -->
                            </x-ui.button>
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.table-empty colspan="5" :title="__('No tags found.')" :message="__('Create a tag to group customers into reusable audiences.')" />
            @endforelse
        </tbody>
    </x-ui.table>

    {{ $tags->links() }}
</x-ui.page>
