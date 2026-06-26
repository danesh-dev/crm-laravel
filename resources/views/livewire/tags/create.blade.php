<x-ui.page
    :title="__('Create Tag')"
    :subtitle="__('Add a reusable label for organizing customers.')"
>
    <x-slot:actions>
        <x-ui.button :href="route('tags.index')" variant="secondary" wire:navigate>
            {{ __('Back') }}
        </x-ui.button>
    </x-slot:actions>

    <form wire:submit="save" class="max-w-2xl rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div>
            <label for="name" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Name') }}</label>
            <input
                id="name"
                type="text"
                wire:model.live="name"
                class="mt-2 h-10 w-full rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700"
                placeholder="{{ __('Example: VIP Customers') }}"
            />
            @error('name')
                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4 rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">
            {{ __('Slug') }}:
            <span class="font-medium text-zinc-950 dark:text-white">{{ $slugPreview ?: '---' }}</span>
        </div>

        <div class="mt-5 flex justify-end">
            <x-ui.button type="submit" icon="plus">
                {{ __('Create Tag') }}
            </x-ui.button>
        </div>
    </form>
</x-ui.page>
