<?php

namespace App\Services\Dashboard;

use App\Enums\EmailDeliveryStatus;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;
use App\Models\EmailTemplate;
use App\Models\Subscription;
use App\Services\Automation\AutomationDisplayService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            'top_automations' => $this->topAutomations(),
            'top_templates' => $this->topTemplates(),
            'recent_customers' => $this->recentCustomers(),
            'recent_deliveries' => $this->recentDeliveries(),
            'active_automations' => $this->activeAutomations(),
            'queue_health' => $this->queueHealth(),
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
            'failed_jobs_today' => $this->failedJobsTodayCount(),
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
                'customer_growth' => $this->singleSeriesChart(
                    __('Customers'),
                    $this->dailyModelCounts(Customer::query(), 'created_at', $start, $end),
                    '#2563eb',
                ),
                'email_sent_failed' => $this->multiSeriesChart([
                    [
                        'label' => __('Sent'),
                        'data' => $this->dailyDeliveryCounts('sent_at', $start, $end, [
                            EmailDeliveryStatus::SENT->value,
                        ]),
                        'color' => '#10b981',
                    ],
                    [
                        'label' => __('Failed'),
                        'data' => $this->dailyDeliveryCounts('failed_at', $start, $end, [
                            EmailDeliveryStatus::FAILED->value,
                            EmailDeliveryStatus::BOUNCED->value,
                        ]),
                        'color' => '#ef4444',
                    ],
                ]),
                'new_subscribers' => $this->singleSeriesChart(
                    __('New Subscribers'),
                    $this->dailyModelCounts(Subscription::query(), 'started_at', $start, $end),
                    '#f59e0b',
                ),
                'automation_runs' => $this->singleSeriesChart(
                    __('Automation Runs'),
                    $this->dailyModelCounts(
                        EmailDelivery::query()->whereNotNull('automation_id'),
                        'created_at',
                        $start,
                        $end,
                    ),
                    '#8b5cf6',
                ),
            ];
        });
    }

    public function topAutomations(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'top_automations', 60, fn (): array => Automation::query()
            ->select(['automations.id', 'automations.name'])
            ->selectRaw('count(email_deliveries.id) as sent_count')
            ->leftJoin('email_deliveries', function ($join): void {
                $join->on('email_deliveries.automation_id', '=', 'automations.id')
                    ->where('email_deliveries.status', EmailDeliveryStatus::SENT->value);
            })
            ->groupBy('automations.id', 'automations.name')
            ->orderByDesc('sent_count')
            ->limit(5)
            ->get()
            ->map(fn (Automation $automation): array => [
                'id' => $automation->id,
                'name' => $automation->name,
                'sent_count' => (int) $automation->sent_count,
            ])
            ->all());
    }

    public function topTemplates(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'top_templates', 60, fn (): array => EmailTemplate::query()
            ->select(['email_templates.id', 'email_templates.name', 'email_templates.subject'])
            ->selectRaw('count(email_deliveries.id) as sent_count')
            ->leftJoin('email_deliveries', function ($join): void {
                $join->on('email_deliveries.email_template_id', '=', 'email_templates.id')
                    ->where('email_deliveries.status', EmailDeliveryStatus::SENT->value);
            })
            ->groupBy('email_templates.id', 'email_templates.name', 'email_templates.subject')
            ->orderByDesc('sent_count')
            ->limit(5)
            ->get()
            ->map(fn (EmailTemplate $template): array => [
                'id' => $template->id,
                'name' => $template->name,
                'subject' => $template->subject,
                'sent_count' => (int) $template->sent_count,
            ])
            ->all());
    }

    public function queueHealth(): array
    {
        return Cache::remember(self::CACHE_PREFIX.'queue_health', 30, fn (): array => [
            'pending_jobs' => DB::table('jobs')->whereNull('reserved_at')->count(),
            'reserved_jobs' => DB::table('jobs')->whereNotNull('reserved_at')->count(),
            'failed_jobs_today' => $this->failedJobsTodayCount(),
            'oldest_pending_at' => $this->oldestPendingJobDate(),
        ]);
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

    private function failedJobsTodayCount(): int
    {
        return DB::table('failed_jobs')
            ->whereDate('failed_at', today())
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

    private function singleSeriesChart(string $label, array $dailyCounts, string $color): array
    {
        return [
            'labels' => array_column($this->dailyRows($dailyCounts), 'label'),
            'datasets' => [
                [
                    'label' => $label,
                    'data' => array_column($this->dailyRows($dailyCounts), 'value'),
                    'borderColor' => $color,
                    'backgroundColor' => $this->transparentColor($color),
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
        ];
    }

    private function multiSeriesChart(array $series): array
    {
        $labels = array_column($this->dailyRows([]), 'label');

        return [
            'labels' => $labels,
            'datasets' => collect($series)
                ->map(function (array $item): array {
                    $rows = $this->dailyRows($item['data']);

                    return [
                        'label' => $item['label'],
                        'data' => array_column($rows, 'value'),
                        'borderColor' => $item['color'],
                        'backgroundColor' => $this->transparentColor($item['color']),
                        'tension' => 0.35,
                    ];
                })
                ->all(),
        ];
    }

    private function dailyRows(array $counts): array
    {
        $start = now()->subDays(29)->startOfDay();

        return collect(range(0, 29))
            ->map(function (int $offset) use ($start, $counts): array {
                $date = $start->copy()->addDays($offset);
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'label' => $date->format('M j'),
                    'value' => (int) ($counts[$key] ?? 0),
                ];
            })
            ->all();
    }

    private function dailyModelCounts($query, string $dateColumn, CarbonInterface $start, CarbonInterface $end): array
    {
        return $query
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$start, $end])
            ->selectRaw("DATE({$dateColumn}) as aggregate_date, count(*) as aggregate")
            ->groupByRaw("DATE({$dateColumn})")
            ->pluck('aggregate', 'aggregate_date')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    private function dailyDeliveryCounts(string $dateColumn, CarbonInterface $start, CarbonInterface $end, array $statuses): array
    {
        return $this->dailyModelCounts(
            EmailDelivery::query()->whereIn('status', $statuses),
            $dateColumn,
            $start,
            $end,
        );
    }

    private function oldestPendingJobDate(): ?string
    {
        $timestamp = DB::table('jobs')
            ->whereNull('reserved_at')
            ->oldest('created_at')
            ->value('created_at');

        return $timestamp
            ? Carbon::createFromTimestamp((int) $timestamp)->format('Y-m-d H:i')
            : null;
    }

    private function transparentColor(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return "rgba({$red}, {$green}, {$blue}, 0.16)";
    }

    private function statusVariant(EmailDeliveryStatus $status): string
    {
        return match ($status) {
            EmailDeliveryStatus::SENT => 'success',
            EmailDeliveryStatus::FAILED,
            EmailDeliveryStatus::BOUNCED => 'danger',
            EmailDeliveryStatus::PROCESSING => 'info',
            EmailDeliveryStatus::CANCELLED => 'neutral',
            EmailDeliveryStatus::PENDING => 'warning',
        };
    }

    private function formatDate(?CarbonInterface $date, string $format = 'Y-m-d H:i'): ?string
    {
        return $date?->format($format);
    }
}
