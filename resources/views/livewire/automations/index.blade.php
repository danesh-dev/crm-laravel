<div class="space-y-4">

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">
            Automations
        </h1>


        <a
            href="{{ route('automations.create') }}"
            class="bg-green-600 text-white px-4 py-2 rounded">
            New Automation
        </a>

    </div>

    <input
        wire:model.live.debounce.300ms="search"
        placeholder="Search..."
        class="border rounded p-2 w-80">

    <table class="w-full border border-gray-300">

        <thead>

            <tr class="bg-gray-100">

                <th class="text-left p-3 border">
                    Name
                </th>

                <th class="text-left p-3 border">
                    Target
                </th>

                <th class="text-left p-3 border">
                    Schedule
                </th>

                <th class="text-left p-3 border">
                    Template
                </th>

                <th class="text-left p-3 border">
                    Active
                </th>

                <th class="text-left p-3 border">
                    Actions
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($automations as $automation)

            <tr class="border-b">

                <td class="p-3">
                    {{ $automation->name }}
                </td>

                <td class="p-3">
                    {{ str($automation->target_user_type->value)->headline() }}
                </td>

                <td class="p-3">

                    @if($automation->schedule_type === \App\Enums\ScheduleType::AFTER_SIGNUP)

                    {{ $automation->schedule->delay_days }}
                    days after signup

                    @elseif($automation->schedule_type === \App\Enums\ScheduleType::DAILY)

                    Daily

                    @elseif($automation->schedule_type === \App\Enums\ScheduleType::WEEKLY)

                    Weekly

                    @elseif($automation->schedule_type === \App\Enums\ScheduleType::MONTHLY)

                    Monthly

                    @endif

                </td>

                <td class="p-3">
                    {{ $automation->emailTemplate?->name }}
                </td>

                <td class="p-3">

                    @if($automation->is_active)

                    <span class="text-green-600 font-medium">
                        Active
                    </span>

                    @else

                    <span class="text-red-600 font-medium">
                        Inactive
                    </span>

                    @endif

                </td>

                <td class="p-3">

                    <div class="flex gap-2">

                        <a
                            href="{{ route('automations.edit', $automation) }}"
                            class="bg-blue-600 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <button
                            wire:click="delete({{ $automation->id }})"
                            wire:confirm="Delete this automation?"
                            class="bg-red-600 text-white px-3 py-1 rounded">
                            Delete
                        </button>

                    </div>

                </td>

            </tr>

            @empty

            <tr>

                <td
                    colspan="6"
                    class="text-center p-6">
                    No automations found.
                </td>

            </tr>

            @endforelse

        </tbody>

    </table>

    {{ $automations->links() }}

</div>