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
        $query = match ($automation->target_user_type) {

            TargetUserType::ALL => Customer::query(),

            TargetUserType::NEWLY_REGISTERED => $this->newlyRegistered($automation),

            TargetUserType::NEVER_SUBSCRIBED => Customer::query()
                ->doesntHave('subscriptions'),

            TargetUserType::ACTIVE_SUBSCRIBER => Customer::query()
                ->whereHas('latestSubscription', fn ($q) => $q->active()),

            TargetUserType::EXPIRED_SUBSCRIBER => $this->expiredSubscribers($automation),

            TargetUserType::CANCELLED_SUBSCRIBER => Customer::query()
                ->whereHas('latestSubscription', fn ($q) => $q->cancelled()),
        };

        return $this->applyTagTargets($query, $automation);
    }

    private function newlyRegistered(Automation $automation): Builder
    {
        $date = now()
            ->subDays($automation->target_offset_days ?? 0)
            ->toDateString();

        return Customer::query()
            ->where(function ($query) use ($date): void {
                $query
                    ->whereDate('signup_at', $date)
                    ->orWhere(function ($query) use ($date): void {
                        $query
                            ->whereNull('signup_at')
                            ->whereDate('created_at', $date);
                    });
            });
    }

    private function expiredSubscribers(Automation $automation): Builder
    {
        return Customer::query()
            ->whereHas('latestSubscription', function ($query) use ($automation): void {
                $query->expired();

                if ($automation->target_offset_days !== null) {
                    $query->whereDate(
                        'expires_at',
                        now()->subDays($automation->target_offset_days)->toDateString()
                    );
                }
            });
    }

    private function applyTagTargets(Builder $query, Automation $automation): Builder
    {
        $tagIds = $automation->tags()
            ->pluck('tags.id');

        foreach ($tagIds as $tagId) {
            $query->whereHas('tags', function (Builder $query) use ($tagId): void {
                $query->where('tags.id', $tagId);
            });
        }

        return $query;
    }
}
