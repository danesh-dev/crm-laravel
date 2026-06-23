<?php

namespace App\Services\Email;

use App\Enums\ScheduleType;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;

class DeliveryDuplicateChecker
{
    public function exists(
        Automation $automation,
        Customer $customer
    ): bool {

        return match ($automation->schedule_type) {

            ScheduleType::AFTER_SIGNUP =>
                $this->afterSignup($automation, $customer),

            ScheduleType::DAILY =>
                $this->daily($automation, $customer),

            ScheduleType::WEEKLY =>
                $this->weekly($automation, $customer),

            ScheduleType::MONTHLY =>
                $this->monthly($automation, $customer),
        };
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
        Customer $customer
    ): bool {
        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->whereDate('scheduled_for', today())
            ->exists();
    }

    protected function weekly(
        Automation $automation,
        Customer $customer
    ): bool {
        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->whereBetween('scheduled_for', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])
            ->exists();
    }

    protected function monthly(
        Automation $automation,
        Customer $customer
    ): bool {
        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->whereMonth('scheduled_for', now()->month)
            ->whereYear('scheduled_for', now()->year)
            ->exists();
    }
}