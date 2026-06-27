<div class="p-4 space-y-4 max-w-lg">

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div>
        <label>Name</label>
        <input type="text" wire:model="name" class="w-full border p-2">
        @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Email</label>
        <input type="email" wire:model="email" class="w-full border p-2">
        @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Phone</label>
        <input type="text" wire:model="phone" class="w-full border p-2">
        @error('phone') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
            {{ __('Tags') }}
        </label>

        <select wire:model="tagIds" multiple class="min-h-32 w-full rounded-md border border-zinc-200 bg-white p-2 text-sm text-zinc-900 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700">
            @foreach($tags as $tag)
                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
            @endforeach
        </select>

        @error('tagIds') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
        @error('tagIds.*') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
    </div>

    <button wire:click="update"
            class="bg-blue-600 text-white px-4 py-2 rounded">
        Update Customer
    </button>

</div>
