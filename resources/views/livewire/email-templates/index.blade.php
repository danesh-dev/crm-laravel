<div class="space-y-4">

    @if(session('success'))
        <div class="p-3 rounded bg-green-100 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center">

        <h1 class="text-2xl font-bold">
            Email Templates
        </h1>

        <a
            href="{{ route('templates.create') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded"
        >
            Create Template
        </a>

    </div>

    <div>
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search templates..."
            class="border rounded p-2 w-full"
        >
    </div>

    <table class="w-full border">

        <thead>
        <tr class="border-b bg-gray-300">
            <th class="text-left p-2">Name</th>
            <th class="text-left p-2">Subject</th>
            <th class="text-left p-2">Created By</th>
            <th class="text-left p-2">Created At</th>
            <th class="text-left p-2">Actions</th>
        </tr>
        </thead>

        <tbody>

        @forelse($templates as $template)

            <tr class="border-b">

                <td class="p-2">
                    {{ $template->name }}
                </td>

                <td class="p-2">
                    {{ $template->subject }}
                </td>

                <td class="p-2">
                    {{ $template->creator?->name }}
                </td>

                <td class="p-2">
                    {{ $template->created_at?->format('Y-m-d H:i') }}
                </td>

                <td class="p-2 space-x-2">

                    <a
                        href="{{ route('templates.edit', $template) }}"
                        class="text-blue-600"
                    >
                        Edit
                    </a>

                    <button
                        wire:click="delete({{ $template->id }})"
                        wire:confirm="Delete this template?"
                        class="text-red-600"
                    >
                        Delete
                    </button>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="5" class="p-4 text-center">
                    No templates found.
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>

    <div>
        {{ $templates->links() }}
    </div>

</div>