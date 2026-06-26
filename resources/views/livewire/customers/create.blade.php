<form wire:submit="save" class="p-4 space-y-2">

    <input wire:model="name" placeholder="Name" class="border p-2 w-full">
    @error('name') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

    <input wire:model="email" placeholder="Email" class="border p-2 w-full">
    @error('email') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

    <input wire:model="phone" placeholder="Phone" class="border p-2 w-full">
    @error('phone') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

    <button type="submit"
            class="bg-blue-500 text-white px-4 py-2">
        Save
    </button>

</form>
