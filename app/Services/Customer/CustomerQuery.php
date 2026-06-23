<?php

namespace App\Services\Customer;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class CustomerQuery
{
    public function apply(array $filters): Builder
    {
        $query = Customer::query()
            ->with(['latestSubscription', 'tags']);

        // Search
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        });

        // Source
        $query->when($filters['source'] ?? null, function ($q, $source) {
            $q->where('source', $source);
        });

        // Tag filter
        $query->when($filters['tag_id'] ?? null, function ($q, $tagId) {
            $q->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        });

        // Subscription filter
        $query->when($filters['subscription'] ?? null, function ($q, $type) {

            if ($type === 'active') {
                $q->whereHas('subscriptions', function ($q) {
                    $q->whereNull('cancelled_at')
                      ->where('expires_at', '>', now());
                });
            }

            if ($type === 'expired') {
                $q->whereHas('subscriptions', function ($q) {
                    $q->where('expires_at', '<=', now());
                });
            }

            if ($type === 'none') {
                $q->whereDoesntHave('subscriptions');
            }
        });

        return $query->latest();
    }
}