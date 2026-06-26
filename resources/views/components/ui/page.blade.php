@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'mx-auto flex w-full max-w-7xl flex-col gap-6 p-4 sm:p-6 lg:p-8']) }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-normal text-zinc-950 dark:text-white">
                {{ $title }}
            </h1>

            @if ($subtitle)
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>

    {{ $slot }}
</div>
