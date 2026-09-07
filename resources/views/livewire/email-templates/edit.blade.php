<x-ui.page
    :title="__('Edit Email Template')"
    :subtitle="$template->name"
>
    <x-slot:actions>
        <x-ui.button :href="route('templates.index')" variant="secondary" wire:navigate>
            {{ __('Back') }}
        </x-ui.button>

        <x-ui.button :href="route('templates.show', $template)" variant="secondary" icon="eye" wire:navigate>
            {{ __('View') }}
        </x-ui.button>
    </x-slot:actions>

    <form wire:submit="save" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Template Details') }}</h2>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <flux:input wire:model="name" :label="__('Name')" required autofocus />
                    <flux:input wire:model="subject" :label="__('Subject')" required />
                </div>
            </section>

            <section>
                <div class="mb-3">
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Body') }}</h2>
                </div>

                @include('livewire.email-templates.partials.editor', ['html' => $body_html])

                @error('body_html') <div class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
            </section>

            @if($preview)
                <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Rendered Preview') }}</h2>

                    <div class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-800 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                        {!! $preview !!}
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="sticky top-6 rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Preview') }}</h2>

                <div class="mt-5 space-y-4">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Automation templates are static. Every recipient receives exactly the same content.') }}</p>

                    <x-ui.button type="button" variant="secondary" class="w-full" wire:click="generatePreview">
                        {{ __('Generate Preview') }}
                    </x-ui.button>
                </div>

                @if(session('success'))
                    <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-6 flex flex-col gap-3">
                    <x-ui.button type="submit" class="w-full">
                        {{ __('Update Template') }}
                    </x-ui.button>

                    <x-ui.button :href="route('templates.index')" variant="secondary" class="w-full" wire:navigate>
                        {{ __('Cancel') }}
                    </x-ui.button>
                </div>
            </div>
        </aside>
    </form>
</x-ui.page>
