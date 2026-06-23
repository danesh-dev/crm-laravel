<?php

namespace App\Services\Automation;

use App\Enums\TargetUserType;
use App\Models\Automation;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class AutomationMatcher
{
    public function query(Automation $automation): Builder
    {
        return match ($automation->target_user_type) {

            TargetUserType::ALL =>
                Customer::query(),

            TargetUserType::NEVER_SUBSCRIBED =>
                Customer::query()
                    ->doesntHave('subscriptions'),

            TargetUserType::ACTIVE_SUBSCRIBER =>
                Customer::query()
                    ->whereHas('subscriptions', fn ($q) => $q->active()),

            TargetUserType::EXPIRED_SUBSCRIBER =>
                Customer::query()
                    ->whereHas('subscriptions', fn ($q) => $q->expired()),

            TargetUserType::CANCELLED_SUBSCRIBER =>
                Customer::query()
                    ->whereHas('subscriptions', fn ($q) => $q->cancelled()),
        };
    }
}