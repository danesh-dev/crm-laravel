<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;
use Illuminate\Database\UniqueConstraintViolationException;

class EmailScheduler
{
    public function __construct(
        private DeliveryDuplicateChecker $duplicateChecker,
        private AutomationRepeatLimiter $repeatLimiter,
    ) {}

    public function schedule(
        Automation $automation,
        Customer $customer,
        \DateTimeInterface $scheduledFor
    ): ?EmailDelivery {

        if (! $this->repeatLimiter->allows($automation, $customer)) {
            return null;
        }

        if ($this->duplicateChecker->exists($automation, $customer, $scheduledFor)) {
            return null;
        }

        try {
            $delivery = EmailDelivery::create([
                'automation_id' => $automation->id,
                'customer_id' => $customer->id,
                'email_template_id' => $automation->email_template_id,
                'status' => EmailDeliveryStatus::PENDING,
                'scheduled_for' => $scheduledFor,
            ]);
        } catch (UniqueConstraintViolationException) {
            return null;
        }

        return $delivery;
    }
}
