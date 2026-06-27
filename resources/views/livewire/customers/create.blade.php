<form wire:submit="save" class="p-4 space-y-2">

    <input wire:model="name" placeholder="Name" class="w-full border p-2">
    @error('name') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

    <input wire:model="email" placeholder="Email" class="w-full border p-2">
    @error('email') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

    <input wire:model="phone" placeholder="Phone" class="w-full border p-2">
    @error('phone') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

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

    <button type="submit"
            class="bg-blue-500 text-white px-4 py-2">
        Save
    </button>

</form>
