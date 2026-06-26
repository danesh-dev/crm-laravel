<x-ui.page
    :title="$template->name"
    :subtitle="$template->subject"
>
    <x-slot:actions>
        <x-ui.button :href="route('templates.index')" variant="secondary" wire:navigate>
            {{ __('Back') }}
        </x-ui.button>

        <x-ui.button :href="route('templates.edit', $template)" icon="pencil-square" wire:navigate>
            {{ __('Edit Template') }}
        </x-ui.button>
    </x-slot:actions>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Template Preview') }}</h2>

            <div class="mt-5 rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-800 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                {!! $template->body_html !!}
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Template Details') }}</h2>

            <dl class="mt-5 space-y-4">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Created By') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $template->creator?->name ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Created At') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $template->created_at?->format('Y-m-d H:i') ?? '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Automations') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ number_format($template->automations_count) }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Deliveries') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ number_format($template->email_deliveries_count) }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-ui.page>
