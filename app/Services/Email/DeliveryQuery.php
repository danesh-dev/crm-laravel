<?php

namespace App\Services\Email;

use App\Models\EmailDelivery;
use Illuminate\Database\Eloquent\Builder;

class DeliveryQuery
{
    public function apply(array $filters): Builder
    {
        return EmailDelivery::query()
            ->with([
                'customer',
                'automation',
                'emailTemplate',
            ])
            ->when(
                $filters['status'] ?? null,
                fn ($q, $status) => $q->where(
                    'status',
                    $status
                )
            )
            ->when(
                $filters['search'] ?? null,
                function ($q, $search) {

                    $q->whereHas(
                        'customer',
                        fn ($customer) => $customer
                            ->where(
                                'name',
                                'ilike',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'ilike',
                                "%{$search}%"
                            )
                    );
                }
            )
            ->latest();
    }
}