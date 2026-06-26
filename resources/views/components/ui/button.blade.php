@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-md font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white disabled:pointer-events-none disabled:opacity-50 dark:focus:ring-offset-zinc-900';

    $sizes = [
        'sm' => 'h-8 px-3 text-sm',
        'md' => 'h-10 px-4 text-sm',
    ];

    $variants = [
        'primary' => 'bg-zinc-900 text-white hover:bg-zinc-700 focus:ring-zinc-900 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 dark:focus:ring-white',
        'secondary' => 'border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 focus:ring-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-600',
        'ghost' => 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 focus:ring-zinc-300 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white',
    ];

    $classes = trim($base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']));
    // dd($icon)
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <flux:icon :icon="$icon" variant="micro" />
        @endif

        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}>
        @if ($icon)
            <flux:icon :icon="$icon" variant="micro" />
        @endif

        {{ $slot }}
    </button>
@endif
