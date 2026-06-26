@props([
    'colspan',
    'title',
    'message' => null,
])

<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-12 text-center">
        <div class="font-medium text-zinc-900 dark:text-white">
            {{ $title }}
        </div>

        @if ($message)
            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ $message }}
            </div>
        @endif
    </td>
</tr>
