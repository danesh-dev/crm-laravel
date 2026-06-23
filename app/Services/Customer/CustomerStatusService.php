<?php

namespace App\Services\Customer;

use App\Enums\TargetUserType;
use App\Models\Customer;

class CustomerStatusService
{
    public function determine(Customer $customer): TargetUserType
    {
        $subscription = $customer->latestSubscription;

        if (! $subscription) {
            return TargetUserType::NEVER_SUBSCRIBED;
        }

        if ($subscription->cancelled_at) {
            return TargetUserType::CANCELLED_SUBSCRIBER;
        }

        if ($subscription->expires_at->isFuture()) {
            return TargetUserType::ACTIVE_SUBSCRIBER;
        }

        return TargetUserType::EXPIRED_SUBSCRIBER;
    }
}