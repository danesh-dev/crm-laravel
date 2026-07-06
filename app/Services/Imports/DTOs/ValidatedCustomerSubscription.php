<?php

namespace App\Services\Imports\DTOs;

use Carbon\CarbonImmutable;

readonly class ValidatedCustomerSubscription
{
    public function __construct(
        public string $email,
        public string $name,
        public ?string $phone,
        public ?CarbonImmutable $signupAt,
        public ?string $providerSubscriptionId,
        public ?string $plan,
        public ?string $status,
        public ?CarbonImmutable $startedAt,
        public ?CarbonImmutable $expiresAt,
    ) {}

    public function hasSubscription(): bool
    {
        return $this->startedAt !== null && $this->expiresAt !== null;
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, ['canceled', 'cancelled'], true);
    }
}
