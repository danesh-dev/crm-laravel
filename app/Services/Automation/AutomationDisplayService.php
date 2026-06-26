<?php

namespace App\Services\Automation;

use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use App\Models\Automation;
use Carbon\CarbonInterface;

class AutomationDisplayService
{
    public function schedule(Automation $automation): string
    {
        return match ($automation->schedule_type) {
            ScheduleType::ONCE => $this->once($automation),
            ScheduleType::AFTER_SIGNUP => trans_choice(
                ':count day after signup|:count days after signup',
                $automation->schedule?->delay_days ?? 0
            ),
            ScheduleType::DAILY => __('Every day at :time', [
                'time' => $this->sendTime($automation),
            ]),
            ScheduleType::WEEKLY => __('Every :day at :time', [
                'day' => $this->weekdayName($automation->schedule?->weekday),
                'time' => $this->sendTime($automation),
            ]),
            ScheduleType::MONTHLY => __('Day :day at :time', [
                'day' => $automation->schedule?->month_day ?? '---',
                'time' => $this->sendTime($automation),
            ]),
        };
    }

    public function scheduleCard(Automation $automation): string
    {
        return match ($automation->schedule_type) {
            ScheduleType::ONCE => $automation->schedule?->run_date
                ? __('Once on :date at :time', [
                    'date' => $automation->schedule->run_date->format('M j Y'),
                    'time' => $this->sendTime($automation),
                ])
                : __('Once'),
            ScheduleType::AFTER_SIGNUP => __(':days after signup at :time', [
                'days' => trans_choice(':count day|:count days', $automation->schedule?->delay_days ?? 0),
                'time' => $this->sendTime($automation),
            ]),
            ScheduleType::DAILY => __('Every day at :time', [
                'time' => $this->sendTime($automation),
            ]),
            ScheduleType::WEEKLY => __('Every :day at :time', [
                'day' => $this->weekdayName($automation->schedule?->weekday),
                'time' => $this->sendTime($automation),
            ]),
            ScheduleType::MONTHLY => __('Every month on day :day at :time', [
                'day' => $automation->schedule?->month_day ?? '---',
                'time' => $this->sendTime($automation),
            ]),
        };
    }

    public function repeat(Automation $automation): string
    {
        return $automation->repeat_count === null
            ? __('Unlimited')
            : trans_choice(':count time per customer|:count times per customer', $automation->repeat_count);
    }

    public function audience(Automation $automation): string
    {
        $label = str($automation->target_user_type->value)->headline()->toString();

        return match ($automation->target_user_type) {
            TargetUserType::NEWLY_REGISTERED => $label.' - '.trans_choice(
                ':count day after signup|:count days after signup',
                $automation->target_offset_days ?? 0
            ),
            TargetUserType::EXPIRED_SUBSCRIBER => $automation->target_offset_days === null
                ? $label
                : $label.' - '.trans_choice(
                    ':count day after expiry|:count days after expiry',
                    $automation->target_offset_days
                ),
            default => $label,
        };
    }

    public function templateShortcutLabel(?Automation $automation): string
    {
        return $automation?->emailTemplate?->name ?? __('Show Template');
    }

    public function sendTime(Automation $automation): string
    {
        $sendTime = $automation->schedule?->send_time;

        if ($sendTime instanceof CarbonInterface) {
            return $sendTime->format('H:i');
        }

        return $sendTime ?: '---';
    }

    public function weekdayName(?int $weekday): string
    {
        return match ($weekday) {
            0 => __('Sunday'),
            1 => __('Monday'),
            2 => __('Tuesday'),
            3 => __('Wednesday'),
            4 => __('Thursday'),
            5 => __('Friday'),
            6 => __('Saturday'),
            default => '---',
        };
    }

    private function once(Automation $automation): string
    {
        $runDate = $automation->schedule?->run_date?->format('M j Y') ?? '---';

        return __(':date at :time', [
            'date' => $runDate,
            'time' => $this->sendTime($automation),
        ]);
    }
}
