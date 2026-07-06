<?php

namespace App\Services\Imports;

use App\Enums\UserSource;
use App\Models\Customer;
use App\Services\Imports\DTOs\ValidatedCustomerSubscription;

class CustomerImportService
{
    /**
     * @return array{customer: Customer, created: bool, updated: bool}
     */
    public function import(ValidatedCustomerSubscription $record): array
    {
        $customer = Customer::query()
            ->withTrashed()
            ->where('email', $record->email)
            ->first();

        if (! $customer) {
            return [
                'customer' => Customer::query()->create([
                    'name' => $record->name,
                    'email' => $record->email,
                    'phone' => $record->phone,
                    'source' => UserSource::MANUAL,
                    'signup_at' => $record->signupAt,
                ]),
                'created' => true,
                'updated' => false,
            ];
        }

        $updates = [];

        if ($customer->trashed()) {
            $customer->restore();
            $updates['deleted_at'] = null;
        }

        if (blank($customer->name) && filled($record->name)) {
            $updates['name'] = $record->name;
        }

        if (blank($customer->phone) && filled($record->phone)) {
            $updates['phone'] = $record->phone;
        }

        if ($customer->signup_at === null && $record->signupAt !== null) {
            $updates['signup_at'] = $record->signupAt;
        }

        if ($customer->source === null) {
            $updates['source'] = UserSource::MANUAL;
        }

        if ($updates !== []) {
            $customer->forceFill($updates)->save();
        }

        return [
            'customer' => $customer->refresh(),
            'created' => false,
            'updated' => $updates !== [],
        ];
    }
}
