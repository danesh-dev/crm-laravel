<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryStatus;
use App\Jobs\SendEmailDeliveryJob;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;

class EmailScheduler
{
    public function schedule(
        Automation $automation,
        Customer $customer,
        \DateTimeInterface $scheduledFor
    ): ?EmailDelivery {

        if (
            $this->alreadyScheduledToday(
                $automation->id,
                $customer->id
            )
        ) {
            return null;
        }

        $delivery = EmailDelivery::create([
            'automation_id' => $automation->id,
            'customer_id' => $customer->id,
            'email_template_id' => $automation->email_template_id,
            'status' => EmailDeliveryStatus::PENDING,
            'scheduled_for' => $scheduledFor,
        ]);

        SendEmailDeliveryJob::dispatch(
            $delivery->id
        );

        return $delivery;
    }

    public function alreadyScheduledToday(
        int $automationId,
        int $customerId
    ): bool {

        return EmailDelivery::query()
            ->where('automation_id', $automationId)
            ->where('customer_id', $customerId)
            ->whereDate('scheduled_for', today())
            ->exists();
    }
}
