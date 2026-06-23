<div class="p-4 space-y-2">

    <input wire:model="name" placeholder="Name" class="border p-2 w-full">

    <input wire:model="email" placeholder="Email" class="border p-2 w-full">

    <input wire:model="phone" placeholder="Phone" class="border p-2 w-full">

    <button wire:click="save"
            class="bg-blue-500 text-white px-4 py-2">
        Save
    </button>

</div>