<?php

namespace App\Services\Dashboard;

use App\Enums\EmailDeliveryStatus;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;
use App\Services\Automation\AutomationDisplayService;
use Carbon\CarbonInterface;
use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    private const CACHE_PREFIX = 'dashboard.v3.';

    public function __construct(
        private AutomationDisplayService $automationDisplay,
    ) {}

    public function get(): array
    {
        return [
            'overview' => $this->overview(),
            'delivery_stats' => $this->deliveryStats(),
            'subscription_stats' => $this->subscriptionStats(),
            'charts' => $this->charts(),
            'recent_customers' => $this->recentCustomers(),
            'recent_deliveries' => $this->recentDeliveries(),
            'active_automations' => $this->activeAutomations(),
        ];
    }

    public function overview(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'overview', 60, fn (): array => [
            'customers' => Customer::query()->count(),
            'active_subscribers' => $this->activeSubscriberCount(),
            'active_automations' => Automation::query()->active()->count(),
            'emails_sent_today' => $this->sentTodayCount(),
            'emails_failed_today' => $this->failedTodayCount(),
            'emails_pending' => $this->pendingCount(),
        ]);
    }

    public function deliveryStats(): array
    {
        return
        Cache::remember(self::CACHE_PREFIX.'delivery_stats', 30, function (): array {
            $sentToday = $this->sentTodayCount();
            $failedToday = $this->failedTodayCount();
            $pending = $this->pendingCount();
            $totalToday = EmailDelivery::query()
                ->whereDate('scheduled_for', today())
                ->count();

            return [
                'success_rate' => $totalToday > 0
                    ? (int) round(($sentToday / $totalToday) * 100)
                    : 0,
                'sent_today' => $sentToday,
                'failed_today' => $failedToday,
                'pending' => $pending,
            ];
        });
    }

    public function subscriptionStats(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'subscription_stats', 30, function (): array {
            $active = $this->customersByLatestSubscription('active');
            $expired = $this->customersByLatestSubscription('expired');
            $cancelled = $this->customersByLatestSubscription('cancelled');
            $neverSubscribed = Customer::query()
                ->whereDoesntHave('subscriptions')
                ->count();

            return [
                'active' => $active,
                'expired' => $expired,
                'cancelled' => $cancelled,
                'never_subscribed' => $neverSubscribed,
                'chart' => [
                    'labels' => [
                        __('Active Subscribers'),
                        __('Expired Subscribers'),
                        __('Cancelled Subscribers'),
                        __('Never Subscribed'),
                    ],
                    'series' => [$active, $expired, $cancelled, $neverSubscribed],
                ],
            ];
        });
    }

    public function charts(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'charts', 30, function (): array {
            $start = now()->subDays(29)->startOfDay();
            $end = now()->endOfDay();

            return [
                'customer_growth' => $this->trendChart(
                    Trend::model(Customer::class)
                        ->between(start: $start, end: $end)
                        ->perDay()
                        ->count()
                ),
                'email_delivery' => $this->trendChart(
                    Trend::query(
                        EmailDelivery::query()
                            ->where('status', EmailDeliveryStatus::SENT->value)
                            ->whereNotNull('sent_at')
                    )
                        ->dateColumn('sent_at')
                        ->between(start: $start, end: $end)
                        ->perDay()
                        ->count()
                ),
            ];
        });
    }

    public function recentCustomers(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'recent_customers', 30, fn (): array => Customer::query()
            ->select(['id', 'name', 'email', 'source', 'signup_at', 'created_at'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'source' => $customer->source?->value,
                'signup_date' => $this->formatDate($customer->signup_at ?? $customer->created_at, 'Y-m-d'),
            ])
            ->all());
    }

    public function recentDeliveries(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'recent_deliveries', 30, fn (): array => EmailDelivery::query()
            ->select([
                'id',
                'automation_id',
                'customer_id',
                'email_template_id',
                'status',
                'scheduled_for',
                'sent_at',
                'created_at',
            ])
            ->with([
                'customer:id,name',
                'automation:id,name',
                'emailTemplate:id,name',
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (EmailDelivery $delivery): array => [
                'id' => $delivery->id,
                'customer' => $delivery->customer?->name,
                'automation' => $delivery->automation?->name,
                'template' => $delivery->emailTemplate?->name,
                'status' => $delivery->status->value,
                'status_label' => str($delivery->status->value)->headline()->toString(),
                'status_variant' => $this->statusVariant($delivery->status),
                'scheduled_for' => $this->formatDate($delivery->scheduled_for),
                'sent_at' => $this->formatDate($delivery->sent_at),
            ])
            ->all());
    }

    public function activeAutomations(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'active_automations', 30, fn (): array => Automation::query()
            ->select([
                'id',
                'name',
                'target_user_type',
                'target_offset_days',
                'schedule_type',
                'is_active',
                'email_template_id',
            ])
            ->active()
            ->with([
                'emailTemplate:id,name',
                'schedule',
            ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Automation $automation): array => [
                'id' => $automation->id,
                'name' => $automation->name,
                'schedule' => $this->automationDisplay->schedule($automation),
                'repeat' => $this->automationDisplay->repeat($automation),
                'target_user_type' => $this->automationDisplay->audience($automation),
                'email_template' => $automation->emailTemplate?->name,
                'is_active' => $automation->is_active,
            ])
            ->all());
    }

    private function sentTodayCount(): int
    {
        return EmailDelivery::query()
            ->whereDate('sent_at', today())
            ->count();
    }

    private function failedTodayCount(): int
    {
        return EmailDelivery::query()
            ->whereDate('failed_at', today())
            ->count();
    }

    private function pendingCount(): int
    {
        return EmailDelivery::query()
            ->pending()
            ->count();
    }

    private function activeSubscriberCount(): int
    {
        return $this->customersByLatestSubscription('active');
    }

    private function customersByLatestSubscription(string $status): int
    {
        return Customer::query()
            ->whereHas('latestSubscription', function ($query) use ($status): void {
                match ($status) {
                    'active' => $query->active(),
                    'expired' => $query->expired(),
                    'cancelled' => $query->cancelled(),
                    default => null,
                };
            })
            ->count();
    }

    private function trendChart($trend): array
    {
        return [
            'labels' => $trend
                ->map(fn ($value): string => Carbon::parse($value->date)->format('M j'))
                ->all(),
            'series' => $trend
                ->map(fn ($value): int => (int) $value->aggregate)
                ->all(),
        ];
    }

    private function statusVariant(EmailDeliveryStatus $status): string
    {
        return match ($status) {
            EmailDeliveryStatus::SENT => 'success',
            EmailDeliveryStatus::FAILED,
            EmailDeliveryStatus::BOUNCED => 'danger',
            EmailDeliveryStatus::PROCESSING => 'info',
            EmailDeliveryStatus::PENDING => 'warning',
        };
    }

    private function formatDate(?CarbonInterface $date, string $format = 'Y-m-d H:i'): ?string
    {
        return $date?->format($format);
    }
}
