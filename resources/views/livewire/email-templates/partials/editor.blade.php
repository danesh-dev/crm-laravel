@php
    $model = $model ?? 'body_html';
    $html = $html ?? '';
    $buttonClass = 'inline-flex h-8 min-w-8 items-center justify-center rounded-md border border-zinc-200 bg-white px-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:ring-zinc-700';
@endphp

<div
    wire:ignore
    data-template-editor
    data-model="{{ $model }}"
    data-initial-html="{{ base64_encode($html) }}"
    class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
>
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/60">
        <div class="flex flex-wrap gap-1">
            <button type="button" class="{{ $buttonClass }}" data-editor-command="bold">B</button>
            <button type="button" class="{{ $buttonClass }} italic" data-editor-command="italic">I</button>
            <button type="button" class="{{ $buttonClass }} underline" data-editor-command="underline">U</button>
        </div>

        <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-700"></div>

        <div class="flex flex-wrap gap-1">
            <button type="button" class="{{ $buttonClass }}" data-editor-block="h2">{{ __('H2') }}</button>
            <button type="button" class="{{ $buttonClass }}" data-editor-block="h3">{{ __('H3') }}</button>
            <button type="button" class="{{ $buttonClass }}" data-editor-block="p">{{ __('P') }}</button>
        </div>

        <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-700"></div>

        <div class="flex flex-wrap gap-1">
            <button type="button" class="{{ $buttonClass }}" data-editor-command="insertUnorderedList">•</button>
            <button type="button" class="{{ $buttonClass }}" data-editor-command="insertOrderedList">1.</button>
            <button type="button" class="{{ $buttonClass }}" data-editor-command="justifyLeft">←</button>
            <button type="button" class="{{ $buttonClass }}" data-editor-command="justifyCenter">↔</button>
            <button type="button" class="{{ $buttonClass }}" data-editor-command="justifyRight">→</button>
        </div>

        <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-700"></div>

        <div class="flex flex-wrap gap-1">
            <button type="button" class="{{ $buttonClass }}" data-editor-command="createLink">{{ __('Link') }}</button>
            <label class="{{ $buttonClass }} cursor-pointer">
                {{ __('Image') }}
                <input type="file" accept="image/*" data-editor-image class="sr-only">
            </label>
        </div>

        <div class="h-6 w-px bg-zinc-200 dark:bg-zinc-700"></div>

        <div class="flex flex-wrap gap-1">
            @foreach(\App\Support\TemplateVariables::all() as $variable)
                <button type="button" class="{{ $buttonClass }}" data-editor-variable="{{ $variable }}">{{ $variable }}</button>
            @endforeach
        </div>

        <div class="ms-auto flex flex-wrap gap-1">
            <button type="button" class="{{ $buttonClass }}" data-editor-mode="design">{{ __('Edit') }}</button>
            <button type="button" class="{{ $buttonClass }}" data-editor-mode="source">{{ __('Source') }}</button>
            <button type="button" class="{{ $buttonClass }}" data-editor-mode="preview">{{ __('Preview') }}</button>
        </div>
    </div>

    <div
        data-editor-surface
        contenteditable="true"
        class="min-h-[28rem] w-full overflow-auto bg-white p-5 text-sm leading-6 text-zinc-900 outline-none dark:bg-zinc-950 dark:text-zinc-100 [&_img]:my-3 [&_img]:max-w-full [&_a]:text-sky-600 [&_a]:underline [&_h2]:mb-3 [&_h2]:mt-5 [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-semibold [&_ol]:ml-5 [&_ol]:list-decimal [&_p]:my-3 [&_ul]:ml-5 [&_ul]:list-disc"
    ></div>

    <textarea
        data-editor-source
        spellcheck="false"
        class="hidden min-h-[28rem] w-full resize-y border-0 bg-zinc-950 p-5 font-mono text-sm leading-6 text-zinc-100 outline-none"
    ></textarea>

    <div
        data-editor-preview
        class="hidden min-h-[28rem] w-full overflow-auto bg-white p-5 text-sm leading-6 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100 [&_img]:my-3 [&_img]:max-w-full [&_a]:text-sky-600 [&_a]:underline [&_h2]:mb-3 [&_h2]:mt-5 [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:mb-2 [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-semibold [&_ol]:ml-5 [&_ol]:list-decimal [&_p]:my-3 [&_ul]:ml-5 [&_ul]:list-disc"
    ></div>
</div>
