@php
    $selectedTags = $tags->whereIn('id', array_map('intval', $tagIds));
@endphp

<x-ui.page
    :title="__('Edit Customer')"
    :subtitle="$customer->email"
>
    <x-slot:actions>
        <x-ui.button :href="route('customers.index')" variant="secondary" wire:navigate>
            {{ __('Back') }}
        </x-ui.button>

        <x-ui.button :href="route('customers.show', $customer)" variant="secondary" icon="eye" wire:navigate>
            {{ __('View') }}
        </x-ui.button>
    </x-slot:actions>

    <form wire:submit="update" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Customer Information') }}</h2>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <flux:input wire:model="name" :label="__('Name')" required autofocus />
                    <flux:input wire:model="email" :label="__('Email')" type="email" required />
                    <flux:input wire:model="phone" :label="__('Phone')" class="md:col-span-2" />
                </div>
            </section>

            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Tags') }}</h2>

                <div class="mt-5">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Assigned Tags') }}</label>
                    <select wire:model.live="tagIds" multiple class="mt-1 min-h-40 w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700">
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>

                    @error('tagIds') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                    @error('tagIds.*') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <div class="sticky top-6 rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Summary') }}</h2>

                <dl class="mt-5 space-y-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $name ?: '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                        <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $email ?: '---' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Selected Tags') }}</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($selectedTags as $tag)
                                    <x-ui.badge>{{ $tag->name }}</x-ui.badge>
                                @empty
                                    <span class="font-medium text-zinc-950 dark:text-white">{{ __('No tags') }}</span>
                                @endforelse
                            </div>
                        </dd>
                    </div>
                </dl>

                @if (session()->has('success'))
                    <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-6 flex flex-col gap-3">
                    <x-ui.button type="submit" class="w-full">
                        {{ __('Update Customer') }}
                    </x-ui.button>

                    <x-ui.button :href="route('customers.index')" variant="secondary" class="w-full" wire:navigate>
                        {{ __('Cancel') }}
                    </x-ui.button>
                </div>
            </div>
        </aside>
    </form>
</x-ui.page>
