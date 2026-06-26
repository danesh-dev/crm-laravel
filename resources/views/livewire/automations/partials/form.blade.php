@php
    $fieldClass = 'h-10 w-full rounded-md border border-zinc-200 bg-white px-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700';
    $textareaClass = 'w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-200 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:border-zinc-500 dark:focus:ring-zinc-700';
    $cardClass = 'cursor-pointer rounded-lg border p-4 transition';
    $checkedClass = 'border-zinc-900 bg-zinc-50 ring-2 ring-zinc-900 dark:border-white dark:bg-zinc-800 dark:ring-white';
    $uncheckedClass = 'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800';

    $targetLabels = [
        \App\Enums\TargetUserType::ALL->value => __('Everyone'),
        \App\Enums\TargetUserType::NEWLY_REGISTERED->value => __('Newly Registered'),
        \App\Enums\TargetUserType::NEVER_SUBSCRIBED->value => __('Never Subscribed'),
        \App\Enums\TargetUserType::ACTIVE_SUBSCRIBER->value => __('Active Subscribers'),
        \App\Enums\TargetUserType::EXPIRED_SUBSCRIBER->value => __('Expired Subscribers'),
        \App\Enums\TargetUserType::CANCELLED_SUBSCRIBER->value => __('Cancelled Subscribers'),
    ];

    $scheduleLabels = [
        \App\Enums\ScheduleType::ONCE->value => __('One Time'),
        \App\Enums\ScheduleType::AFTER_SIGNUP->value => __('After Signup'),
        \App\Enums\ScheduleType::DAILY->value => __('Daily'),
        \App\Enums\ScheduleType::WEEKLY->value => __('Weekly'),
        \App\Enums\ScheduleType::MONTHLY->value => __('Monthly'),
    ];

    $weekdayNames = [
        0 => __('Sunday'),
        1 => __('Monday'),
        2 => __('Tuesday'),
        3 => __('Wednesday'),
        4 => __('Thursday'),
        5 => __('Friday'),
        6 => __('Saturday'),
    ];

    $templateName = $selectedTemplate?->name ?? __('No template selected');
    $repeatSummary = $repeat_mode === 'limited'
        ? trans_choice(':count time per customer|:count times per customer', (int) $repeat_count)
        : __('Unlimited');
@endphp

<form wire:submit="save" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
    <div class="space-y-6">
        <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Basic Information') }}</h2>

                <label class="inline-flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    <input type="checkbox" wire:model.live="is_active" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900 dark:border-zinc-600 dark:bg-zinc-950">
                    {{ __('Active') }}
                </label>
            </div>

            <div class="mt-5 grid gap-4">
                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Name') }}</label>
                    <input type="text" wire:model.live.debounce.300ms="name" class="{{ $fieldClass }}">
                    @error('name') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Description') }}</label>
                    <textarea wire:model.live.debounce.300ms="description" rows="3" class="{{ $textareaClass }}"></textarea>
                    @error('description') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Audience') }}</h2>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($targetTypes as $type)
                    <label class="{{ $cardClass }} {{ $target_user_type === $type->value ? $checkedClass : $uncheckedClass }}">
                        <input type="radio" wire:model.live="target_user_type" value="{{ $type->value }}" class="sr-only">
                        <span class="flex items-start gap-3">
                            <span class="mt-1 size-3 rounded-full border {{ $target_user_type === $type->value ? 'border-zinc-900 bg-zinc-900 dark:border-white dark:bg-white' : 'border-zinc-400' }}"></span>
                            <span class="font-medium text-zinc-950 dark:text-white">{{ $targetLabels[$type->value] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            @error('target_user_type') <div class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror

            @if($target_user_type === \App\Enums\TargetUserType::NEWLY_REGISTERED->value)
                <div class="mt-5 max-w-xs">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Days after signup') }}</label>
                    <input type="number" min="0" max="32767" wire:model.live.number="target_offset_days" class="{{ $fieldClass }}">
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Use 0 for people who registered today.') }}</p>
                    @error('target_offset_days') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                </div>
            @endif

            @if($target_user_type === \App\Enums\TargetUserType::EXPIRED_SUBSCRIBER->value)
                <div class="mt-5 max-w-xs">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Days after expiry') }}</label>
                    <input type="number" min="0" max="32767" wire:model.live.number="target_offset_days" placeholder="{{ __('Any expired') }}" class="{{ $fieldClass }}">
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Leave empty for all expired subscribers, or use 7 for subscriptions that expired seven days ago.') }}</p>
                    @error('target_offset_days') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                </div>
            @endif
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Schedule') }}</h2>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @foreach($scheduleTypes as $type)
                    <label class="{{ $cardClass }} {{ $schedule_type === $type->value ? $checkedClass : $uncheckedClass }}">
                        <input type="radio" wire:model.live="schedule_type" value="{{ $type->value }}" class="sr-only">
                        <span class="flex items-start gap-3">
                            <span class="mt-1 size-3 rounded-full border {{ $schedule_type === $type->value ? 'border-zinc-900 bg-zinc-900 dark:border-white dark:bg-white' : 'border-zinc-400' }}"></span>
                            <span class="font-medium text-zinc-950 dark:text-white">{{ $scheduleLabels[$type->value] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @if($schedule_type === \App\Enums\ScheduleType::ONCE->value)
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Run Date') }}</label>
                        <input type="date" wire:model.live="run_date" class="{{ $fieldClass }}">
                        @error('run_date') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                    </div>
                @endif

                @if($schedule_type === \App\Enums\ScheduleType::AFTER_SIGNUP->value)
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Delay Days') }}</label>
                        <input type="number" min="0" wire:model.live.number="delay_days" class="{{ $fieldClass }}">
                        @error('delay_days') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                    </div>
                @endif

                @if($schedule_type === \App\Enums\ScheduleType::WEEKLY->value)
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Weekday') }}</label>
                        <select wire:model.live.number="weekday" class="{{ $fieldClass }}">
                            <option value="">{{ __('Select weekday') }}</option>
                            <option value="0">{{ __('Sunday') }}</option>
                            <option value="1">{{ __('Monday') }}</option>
                            <option value="2">{{ __('Tuesday') }}</option>
                            <option value="3">{{ __('Wednesday') }}</option>
                            <option value="4">{{ __('Thursday') }}</option>
                            <option value="5">{{ __('Friday') }}</option>
                            <option value="6">{{ __('Saturday') }}</option>
                        </select>
                        @error('weekday') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                    </div>
                @endif

                @if($schedule_type === \App\Enums\ScheduleType::MONTHLY->value)
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Day Of Month') }}</label>
                        <input type="number" min="1" max="31" wire:model.live.number="month_day" class="{{ $fieldClass }}">
                        @error('month_day') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                    </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Send Time') }}</label>
                    <input type="time" wire:model.live="send_time" class="{{ $fieldClass }}">
                    @error('send_time') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Email Template') }}</h2>

            <div class="mt-5">
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Template') }}</label>
                <select wire:model.live="email_template_id" class="{{ $fieldClass }}">
                    <option value="">{{ __('Select Template') }}</option>

                    @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                    @endforeach
                </select>
                @error('email_template_id') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror

                @if($selectedTemplate)
                    <div class="mt-3">
                        <x-ui.button :href="route('templates.show', $selectedTemplate)" variant="secondary" size="sm" icon="eye" wire:navigate>
                            {{ __('Show Template') }}
                        </x-ui.button>
                    </div>
                @endif
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Subject Preview') }}</div>
                    <div class="mt-2 font-medium text-zinc-950 dark:text-white">{{ $selectedTemplate?->subject ?? '---' }}</div>
                </div>

                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950">
                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Body Preview') }}</div>
                    <div class="mt-2 max-h-56 overflow-y-auto text-sm text-zinc-700 dark:text-zinc-200">
                        @if($selectedTemplate)
                            {!! $selectedTemplate->body_html !!}
                        @else
                            ---
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Repeat') }}</h2>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <label class="{{ $cardClass }} {{ $repeat_mode === 'unlimited' ? $checkedClass : $uncheckedClass }}">
                    <input type="radio" wire:model.live="repeat_mode" value="unlimited" class="sr-only">
                    <span class="font-medium text-zinc-950 dark:text-white">{{ __('Unlimited') }}</span>
                </label>

                <label class="{{ $cardClass }} {{ $repeat_mode === 'limited' ? $checkedClass : $uncheckedClass }}">
                    <input type="radio" wire:model.live="repeat_mode" value="limited" class="sr-only">
                    <span class="font-medium text-zinc-950 dark:text-white">{{ __('Limit number of sends') }}</span>
                </label>
            </div>

            @if($repeat_mode === 'limited')
                <div class="mt-4 max-w-xs">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Number of sends') }}</label>
                    <input type="number" min="1" max="32767" wire:model.live.number="repeat_count" class="{{ $fieldClass }}">
                    @error('repeat_count') <div class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</div> @enderror
                </div>
            @endif
        </section>
    </div>

    <aside class="space-y-6">
        <div class="sticky top-6 rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-950 dark:text-white">{{ __('Summary') }}</h2>

            <dl class="mt-5 space-y-4">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Automation') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $name ?: '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Audience') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">
                        {{ $targetLabels[$target_user_type] ?? '---' }}

                        @if($target_user_type === \App\Enums\TargetUserType::NEWLY_REGISTERED->value)
                            <span class="block text-sm font-normal text-zinc-500 dark:text-zinc-400">
                                {{ trans_choice(':count day after signup|:count days after signup', (int) $target_offset_days) }}
                            </span>
                        @endif

                        @if($target_user_type === \App\Enums\TargetUserType::EXPIRED_SUBSCRIBER->value)
                            <span class="block text-sm font-normal text-zinc-500 dark:text-zinc-400">
                                {{ $target_offset_days === null ? __('Any expired subscriber') : trans_choice(':count day after expiry|:count days after expiry', (int) $target_offset_days) }}
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Schedule') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">
                        @if($schedule_type === \App\Enums\ScheduleType::ONCE->value)
                            {{ $run_date ?: '---' }}
                        @elseif($schedule_type === \App\Enums\ScheduleType::AFTER_SIGNUP->value)
                            {{ trans_choice(':count day after signup|:count days after signup', (int) $delay_days) }}
                        @elseif($schedule_type === \App\Enums\ScheduleType::DAILY->value)
                            {{ __('Every day') }}
                        @elseif($schedule_type === \App\Enums\ScheduleType::WEEKLY->value)
                            {{ __('Every :day', ['day' => $weekday !== null && $weekday !== '' ? $weekdayNames[(int) $weekday] : '---']) }}
                        @elseif($schedule_type === \App\Enums\ScheduleType::MONTHLY->value)
                            {{ __('Day :day', ['day' => $month_day ?: '---']) }}
                        @else
                            ---
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Send Time') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $send_time ?: '---' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Template') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $templateName }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Repeat') }}</dt>
                    <dd class="mt-1 font-medium text-zinc-950 dark:text-white">{{ $repeatSummary }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                    <dd class="mt-1">
                        <x-ui.badge :variant="$is_active ? 'success' : 'neutral'">
                            {{ $is_active ? __('Active') : __('Inactive') }}
                        </x-ui.badge>
                    </dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-col gap-3">
                <x-ui.button type="submit" class="w-full">
                    {{ $submitLabel }}
                </x-ui.button>

                <x-ui.button :href="route('automations.index')" variant="secondary" class="w-full" wire:navigate>
                    {{ __('Cancel') }}
                </x-ui.button>
            </div>
        </div>
    </aside>
</form>
