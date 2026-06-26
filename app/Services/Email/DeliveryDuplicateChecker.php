<?php

namespace App\Services\Email;

use App\Enums\ScheduleType;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;
use Carbon\Carbon;
use DateTimeInterface;

class DeliveryDuplicateChecker
{
    public function exists(
        Automation $automation,
        Customer $customer,
        DateTimeInterface $scheduledFor
    ): bool {

        return match ($automation->schedule_type) {

            ScheduleType::ONCE => $this->once($automation, $customer),

            ScheduleType::AFTER_SIGNUP => $this->afterSignup($automation, $customer),

            ScheduleType::DAILY => $this->daily($automation, $customer, $scheduledFor),

            ScheduleType::WEEKLY => $this->weekly($automation, $customer, $scheduledFor),

            ScheduleType::MONTHLY => $this->monthly($automation, $customer, $scheduledFor),
        };
    }

    protected function once(
        Automation $automation,
        Customer $customer
    ): bool {
        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->exists();
    }

    protected function afterSignup(
        Automation $automation,
        Customer $customer
    ): bool {
        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->exists();
    }

    protected function daily(
        Automation $automation,
        Customer $customer,
        DateTimeInterface $scheduledFor
    ): bool {
        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->whereDate('scheduled_for', $scheduledFor)
            ->exists();
    }

    protected function weekly(
        Automation $automation,
        Customer $customer,
        DateTimeInterface $scheduledFor
    ): bool {
        $date = Carbon::parse($scheduledFor);

        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->whereBetween('scheduled_for', [
                $date->copy()->startOfWeek(),
                $date->copy()->endOfWeek(),
            ])
            ->exists();
    }

    protected function monthly(
        Automation $automation,
        Customer $customer,
        DateTimeInterface $scheduledFor
    ): bool {
        $date = Carbon::parse($scheduledFor);

        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->whereMonth('scheduled_for', $date->month)
            ->whereYear('scheduled_for', $date->year)
            ->exists();
    }
}
