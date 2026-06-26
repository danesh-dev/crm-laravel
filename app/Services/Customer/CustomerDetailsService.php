<?php

namespace App\Services\Customer;

use App\Enums\EmailDeliveryStatus;
use App\Models\Customer;
use App\Models\EmailDelivery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerDetailsService
{
    public function statistics(Customer $customer): array
    {
        $statusCounts = EmailDelivery::query()
            ->where('customer_id', $customer->id)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total_emails' => (int) $statusCounts->sum(),
            'sent' => $this->statusCount($statusCounts, EmailDeliveryStatus::SENT),
            'failed' => $this->statusCount($statusCounts, EmailDeliveryStatus::FAILED)
                + $this->statusCount($statusCounts, EmailDeliveryStatus::BOUNCED),
            'pending' => $this->statusCount($statusCounts, EmailDeliveryStatus::PENDING),
        ];
    }

    public function subscriptionHistory(Customer $customer): Collection
    {
        return $customer->subscriptions()
            ->latest('started_at')
            ->get(['id', 'customer_id', 'started_at', 'expires_at', 'cancelled_at', 'created_at']);
    }

    public function emailHistory(Customer $customer, int $perPage = 10): LengthAwarePaginator
    {
        return $customer->emailDeliveries()
            ->with([
                'automation:id,name',
                'emailTemplate:id,name',
            ])
            ->latest()
            ->paginate($perPage);
    }

    public function upcomingEmails(Customer $customer): Collection
    {
        return $customer->emailDeliveries()
            ->with([
                'automation:id,name',
                'emailTemplate:id,name',
            ])
            ->where('status', EmailDeliveryStatus::PENDING->value)
            ->where('scheduled_for', '>=', now())
            ->orderBy('scheduled_for')
            ->limit(8)
            ->get([
                'id',
                'automation_id',
                'customer_id',
                'email_template_id',
                'status',
                'scheduled_for',
                'created_at',
            ]);
    }

    public function timeline(Customer $customer): array
    {
        return collect()
            ->when($customer->signup_at ?? $customer->created_at, function (Collection $items) use ($customer): void {
                $items->push([
                    'type' => __('Signup'),
                    'title' => __('Customer signed up'),
                    'description' => $customer->source
                        ? __('Source: :source', ['source' => str($customer->source->value)->headline()])
                        : null,
                    'date' => $customer->signup_at ?? $customer->created_at,
                ]);
            })
            ->merge($this->subscriptionTimeline($customer))
            ->merge($this->deliveryTimeline($customer))
            ->filter(fn (array $item): bool => $item['date'] !== null)
            ->sortByDesc('date')
            ->values()
            ->all();
    }

    private function subscriptionTimeline(Customer $customer): Collection
    {
        return $customer->subscriptions()
            ->latest('created_at')
            ->limit(20)
            ->get(['id', 'started_at', 'expires_at', 'cancelled_at', 'created_at'])
            ->flatMap(function ($subscription): array {
                return array_filter([
                    [
                        'type' => __('Subscription'),
                        'title' => __('Subscription started'),
                        'description' => $subscription->expires_at
                            ? __('Expires :date', ['date' => $subscription->expires_at->format('Y-m-d H:i')])
                            : null,
                        'date' => $subscription->started_at ?? $subscription->created_at,
                    ],
                    $subscription->cancelled_at ? [
                        'type' => __('Subscription'),
                        'title' => __('Subscription cancelled'),
                        'description' => null,
                        'date' => $subscription->cancelled_at,
                    ] : null,
                    $subscription->expires_at ? [
                        'type' => __('Subscription'),
                        'title' => __('Subscription expired'),
                        'description' => null,
                        'date' => $subscription->expires_at,
                    ] : null,
                ]);
            });
    }

    private function deliveryTimeline(Customer $customer): Collection
    {
        return $customer->emailDeliveries()
            ->with([
                'automation:id,name',
                'emailTemplate:id,name',
            ])
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (EmailDelivery $delivery): array => [
                'type' => __('Email'),
                'title' => str($delivery->status->value)->headline()->toString(),
                'description' => collect([
                    $delivery->automation?->name,
                    $delivery->emailTemplate?->name,
                ])->filter()->join(' - '),
                'date' => $delivery->sent_at
                    ?? $delivery->failed_at
                    ?? $delivery->processing_at
                    ?? $delivery->scheduled_for
                    ?? $delivery->created_at,
            ]);
    }

    private function statusCount(Collection $counts, EmailDeliveryStatus $status): int
    {
        return (int) ($counts[$status->value] ?? 0);
    }
}
