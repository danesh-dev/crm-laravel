<?php

namespace App\Services\Imports;

use App\Models\Customer;
use App\Models\Subscription;
use App\Services\Imports\DTOs\ValidatedCustomerSubscription;

class SubscriptionImportService
{
    /**
     * @return array{created: bool, duplicate: bool}
     */
    public function import(Customer $customer, ValidatedCustomerSubscription $record): array
    {
        if (! $record->hasSubscription()) {
            return [
                'created' => false,
                'duplicate' => false,
            ];
        }

        $duplicateQuery = Subscription::query()
            ->where('customer_id', $customer->id)
            ->where('started_at', $record->startedAt)
            ->where('expires_at', $record->expiresAt);

        filled($record->plan)
            ? $duplicateQuery->where('plan', $record->plan)
            : $duplicateQuery->whereNull('plan');

        if ($duplicateQuery->exists()) {
            return [
                'created' => false,
                'duplicate' => true,
            ];
        }

        Subscription::query()->create([
            'customer_id' => $customer->id,
            'provider_subscription_id' => $record->providerSubscriptionId,
            'plan' => $record->plan,
            'started_at' => $record->startedAt,
            'expires_at' => $record->expiresAt,
            'cancelled_at' => $record->isCancelled() ? $record->expiresAt : null,
        ]);

        return [
            'created' => true,
            'duplicate' => false,
        ];
    }
}
