<div class="p-4 space-y-4">

    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">

        <input type="text" wire:model.live="search"
               placeholder="Search name, email..."
               class="border p-2 rounded" />

        <select wire:model.live="subscription" class="border p-2 rounded">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="expired">Expired</option>
            <option value="none">No Subscription</option>
        </select>

        <input type="text" wire:model.live="source"
               placeholder="Source"
               class="border p-2 rounded" />

        <input type="number" wire:model.live="tag_id"
               placeholder="Tag ID"
               class="border p-2 rounded" />

    </div>

    {{-- Table --}}
    <table class="w-full border">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($customers as $customer)
                <tr class="border-t">
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->source }}</td>

                    <td class="space-x-2">
                        <a href="{{ route('customers.edit', $customer->id) }}"
                        class="text-blue-600 hover:underline">
                            Edit
                        </a>

                        <button
                            wire:click="delete({{ $customer->id }})"
                            wire:confirm="Are you sure you want to delete this customer?"
                            class="text-red-600 hover:underline">
                            Delete
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>

</div>