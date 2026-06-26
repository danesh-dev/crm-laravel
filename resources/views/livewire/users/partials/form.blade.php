@php
    $fieldClass = 'h-10 w-full rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700';
@endphp

<form wire:submit="save" class="max-w-2xl space-y-6 rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
    <div>
        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Name') }}</label>
        <input type="text" wire:model="name" class="{{ $fieldClass }}">
        @error('name') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
    </div>

    <div>
        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Email') }}</label>
        <input type="email" wire:model="email" class="{{ $fieldClass }}">
        @error('email') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                {{ $passwordRequired ? __('Password') : __('New Password') }}
            </label>
            <input type="password" wire:model="password" class="{{ $fieldClass }}">
            @error('password') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Confirm Password') }}</label>
            <input type="password" wire:model="password_confirmation" class="{{ $fieldClass }}">
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Role') }}</label>
        <select wire:model="role" @disabled(! $canEditRole) class="{{ $fieldClass }}">
            @foreach($roles as $roleName)
                <option value="{{ $roleName }}">{{ $roleName }}</option>
            @endforeach
        </select>
        @if(! $canEditRole)
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('You cannot edit your own role.') }}</p>
        @endif
        @error('role') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
    </div>

    <div class="flex flex-wrap gap-3">
        <x-ui.button type="submit">
            {{ $submitLabel }}
        </x-ui.button>

        <x-ui.button :href="route('users.index')" variant="secondary" wire:navigate>
            {{ __('Cancel') }}
        </x-ui.button>
    </div>
</form>
