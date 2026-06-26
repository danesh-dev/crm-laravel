<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;

class AutomationRepeatLimiter
{
    public function allows(Automation $automation, Customer $customer): bool
    {
        if ($automation->repeat_count === null) {
            return true;
        }

        return $this->successfulDeliveryCount($automation, $customer) < $automation->repeat_count;
    }

    private function successfulDeliveryCount(Automation $automation, Customer $customer): int
    {
        return EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->where('customer_id', $customer->id)
            ->where('status', EmailDeliveryStatus::SENT->value)
            ->count();
    }
}
