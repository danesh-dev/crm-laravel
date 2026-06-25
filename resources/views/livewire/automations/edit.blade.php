<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold">
            Edit Automation
        </h1>
    </div>

    <div>
        <label class="block mb-1">
            Name
        </label>

        <input
            type="text"
            wire:model="name"
            class="border p-2 w-full"
        >

        @error('name')
            <div class="text-red-600">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div>
        <label class="block mb-1">
            Description
        </label>

        <textarea
            wire:model="description"
            rows="3"
            class="border p-2 w-full"
        ></textarea>
    </div>

    <div>
        <label class="block mb-1">
            Target Users
        </label>

        <select
            wire:model.live="target_user_type"
            class="border p-2 w-full"
        >

            @foreach($targetTypes as $type)

                <option value="{{ $type->value }}">
                    {{ str($type->value)->headline() }}
                </option>

            @endforeach

        </select>
    </div>

    <div>
        <label class="block mb-1">
            Schedule Type
        </label>

        <select
            wire:model.live="schedule_type"
            class="border p-2 w-full"
        >

            @foreach($scheduleTypes as $type)

                <option value="{{ $type->value }}">
                    {{ str($type->value)->headline() }}
                </option>

            @endforeach

        </select>
    </div>

    <div>
        <label class="block mb-1">
            Email Template
        </label>

        <select
            wire:model="email_template_id"
            class="border p-2 w-full"
        >

            <option value="">
                Select Template
            </option>

            @foreach($templates as $template)

                <option value="{{ $template->id }}">
                    {{ $template->name }}
                </option>

            @endforeach

        </select>

        @error('email_template_id')
            <div class="text-red-600">
                {{ $message }}
            </div>
        @enderror
    </div>

    {{-- AFTER SIGNUP --}}

    @if($schedule_type === \App\Enums\ScheduleType::AFTER_SIGNUP->value)

        <div>
            <label class="block mb-1">
                Days After Signup
            </label>

            <input
                type="number"
                min="0"
                wire:model="delay_days"
                class="border p-2 w-full"
            >
        </div>

    @endif

    {{-- WEEKLY --}}

    @if($schedule_type === \App\Enums\ScheduleType::WEEKLY->value)

        <div>

            <label class="block mb-1">
                Weekday
            </label>

            <select
                wire:model="weekday"
                class="border p-2 w-full"
            >

                <option value="0">Sunday</option>
                <option value="1">Monday</option>
                <option value="2">Tuesday</option>
                <option value="3">Wednesday</option>
                <option value="4">Thursday</option>
                <option value="5">Friday</option>
                <option value="6">Saturday</option>

            </select>

        </div>

    @endif

    {{-- MONTHLY --}}

    @if($schedule_type === \App\Enums\ScheduleType::MONTHLY->value)

        <div>

            <label class="block mb-1">
                Day Of Month
            </label>

            <input
                type="number"
                min="1"
                max="31"
                wire:model="month_day"
                class="border p-2 w-full"
            >

        </div>

    @endif

    {{-- SEND TIME --}}

    <div>

        <label class="block mb-1">
            Send Time
        </label>

        <input
            type="time"
            wire:model="send_time"
            class="border p-2 w-full"
        >

    </div>

    {{-- ACTIVE --}}

    <div>

        <label class="flex items-center gap-2">

            <input
                type="checkbox"
                wire:model="is_active"
            >

            <span>
                Active
            </span>

        </label>

    </div>

    {{-- SUMMARY --}}

    <div class="border p-4">

        <h3 class="font-bold mb-2">
            Summary
        </h3>

        <div>

            Target:
            {{ str($target_user_type)->headline() }}

        </div>

        <div>

            Schedule:

            @if($schedule_type === \App\Enums\ScheduleType::AFTER_SIGNUP->value)

                {{ $delay_days ?: 0 }}
                days after signup

            @elseif($schedule_type === \App\Enums\ScheduleType::DAILY->value)

                Daily

            @elseif($schedule_type === \App\Enums\ScheduleType::WEEKLY->value)

                Weekly

            @elseif($schedule_type === \App\Enums\ScheduleType::MONTHLY->value)

                Monthly

            @endif

            at

            {{ $send_time }}

        </div>

    </div>

    <div>

        <button
            type="button"
            wire:click="save"
            class="border px-4 py-2"
        >
            Edit Automation
        </button>

    </div>

</div>