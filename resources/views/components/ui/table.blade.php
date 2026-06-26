<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-800">
            {{ $slot }}
        </table>
    </div>
</div>
