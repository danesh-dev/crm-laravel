<?php

namespace App\Services\Imports;

use App\Services\Imports\DTOs\ValidatedCustomerSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class JsonImportValidator
{
    private const VALID_STATUSES = [
        'active',
        'trialing',
        'canceled',
        'cancelled',
        'expired',
        'past_due',
        'unpaid',
        'incomplete',
        'incomplete_expired',
    ];

    /**
     * @param  array<string, mixed>  $row
     * @return array{record: ValidatedCustomerSubscription|null, errors: array<int, string>}
     */
    public function validate(array $row): array
    {
        $hasSubscription = $this->hasSubscriptionData($row);
        $row = $this->normalize($row);

        $rules = [
            'email' => ['required', 'email'],
            'signUpDate' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'subscriptionId' => ['nullable', 'string', 'max:255'],
            'plan' => ['nullable', 'string', 'max:255'],
            'status' => [$hasSubscription ? 'required' : 'nullable', 'string', Rule::in(self::VALID_STATUSES)],
            'subscriptionStart' => [$hasSubscription ? 'required' : 'nullable', 'date'],
            'subscriptionEnd' => [$hasSubscription ? 'required' : 'nullable', 'date', 'after_or_equal:subscriptionStart'],
        ];

        $validator = Validator::make($row, $rules);

        if ($validator->fails()) {
            return [
                'record' => null,
                'errors' => $validator->errors()->all(),
            ];
        }

        $email = strtolower(trim((string) $row['email']));

        return [
            'record' => new ValidatedCustomerSubscription(
                email: $email,
                name: filled($row['name'] ?? null) ? (string) $row['name'] : $email,
                phone: filled($row['phone'] ?? null) ? (string) $row['phone'] : null,
                signupAt: filled($row['signUpDate'] ?? null) ? CarbonImmutable::parse($row['signUpDate']) : null,
                providerSubscriptionId: filled($row['subscriptionId'] ?? null) ? (string) $row['subscriptionId'] : null,
                plan: filled($row['plan'] ?? null) ? (string) $row['plan'] : null,
                status: filled($row['status'] ?? null) ? (string) $row['status'] : null,
                startedAt: filled($row['subscriptionStart'] ?? null) ? CarbonImmutable::parse($row['subscriptionStart']) : null,
                expiresAt: filled($row['subscriptionEnd'] ?? null) ? CarbonImmutable::parse($row['subscriptionEnd']) : null,
            ),
            'errors' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function hasSubscriptionData(array $row): bool
    {
        return filled($row['subscriptionId'] ?? null)
            || filled($row['status'] ?? null)
            || filled($row['subscriptionStart'] ?? null)
            || filled($row['subscriptionEnd'] ?? null)
            || filled($row['plan'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalize(array $row): array
    {
        if (filled($row['status'] ?? null)) {
            $row['status'] = strtolower(trim((string) $row['status']));
        }

        if (filled($row['email'] ?? null)) {
            $row['email'] = strtolower(trim((string) $row['email']));
        }

        return $row;
    }
}
