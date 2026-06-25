<div class="space-y-4">

    <div class="flex gap-4">

        <input
            wire:model.live.debounce.300ms="search"
            placeholder="Search customer..."
            class="border rounded p-2">

        <select
            wire:model.live="status"
            class="border rounded p-2">

            <option value="">
                All Statuses
            </option>

            <option value="pending">
                Pending
            </option>

            <option value="processing">
                Processing
            </option>

            <option value="sent">
                Sent
            </option>

            <option value="failed">
                Failed
            </option>

        </select>

    </div>

    <table class="w-full border">

        <thead>

            <tr class="bg-gray-100">

                <th class="text-left p-3">
                    Customer
                </th>

                <th class="text-left p-3">
                    Template
                </th>

                <th class="text-left p-3">
                    Automation
                </th>

                <th class="text-left p-3">
                    Status
                </th>

                <th class="text-left p-3">
                    Scheduled
                </th>

                <th class="text-left p-3">
                    Sent
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($deliveries as $delivery)

            <tr class="border-t">

                <td class="p-3">

                    {{ $delivery->customer?->name }}

                    <br>

                    <small>
                        {{ $delivery->customer?->email }}
                    </small>

                </td>

                <td class="p-3">
                    {{ $delivery->emailTemplate?->name }}
                </td>

                <td class="p-3">
                    {{ $delivery->automation?->name ?? '---' }}
                </td>

                <td class="p-3">
                    {{ ucfirst($delivery->status->value) }}
                </td>

                <td class="p-3">
                    {{ $delivery->scheduled_for }}
                </td>

                <td class="p-3">
                    {{ $delivery->sent_at }}
                </td>

                @if($delivery->status->value === 'sent')

                <button
                    wire:click="retry({{ $delivery->id }})"
                    class="border px-2 py-1">
                    Retry
                </button>

                @endif

            </tr>

            @endforeach

        </tbody>

    </table>

    {{ $deliveries->links() }}

</div>