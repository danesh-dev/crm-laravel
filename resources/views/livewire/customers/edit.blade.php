<div class="p-4 space-y-4 max-w-lg">

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div>
        <label>Name</label>
        <input type="text" wire:model="name" class="border p-2 w-full">
        @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Email</label>
        <input type="email" wire:model="email" class="border p-2 w-full">
        @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Phone</label>
        <input type="text" wire:model="phone" class="border p-2 w-full">
        @error('phone') <span class="text-red-500">{{ $message }}</span> @enderror
    </div>

    <button wire:click="update"
            class="bg-blue-600 text-white px-4 py-2 rounded">
        Update Customer
    </button>

</div>