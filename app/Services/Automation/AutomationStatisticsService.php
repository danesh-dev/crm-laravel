<?php

namespace App\Services\Automation;

use App\Enums\EmailDeliveryStatus;
use App\Enums\ScheduleType;
use App\Models\Automation;
use App\Models\EmailDelivery;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AutomationStatisticsService
{
    private const CACHE_SECONDS = 300;

    public function __construct(
        private AutomationMatcher $matcher,
    ) {}

    public function get(Automation $automation): array
    {
        return Cache::remember(
            $this->cacheKey($automation),
            self::CACHE_SECONDS,
            fn (): array => [
                'summary' => $this->summary($automation),
                'daily' => $this->dailyChartData($automation),
            ],
        );
    }

    public function forget(Automation $automation): void
    {
        Cache::forget($this->cacheKey($automation));
    }

    private function summary(Automation $automation): array
    {
        $statusCounts = EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $sent = $this->statusCount($statusCounts, EmailDeliveryStatus::SENT);
        $failed = $this->statusCount($statusCounts, EmailDeliveryStatus::FAILED)
            + $this->statusCount($statusCounts, EmailDeliveryStatus::BOUNCED);
        $totalCompleted = $sent + $failed;

        return [
            'total_queued' => (int) $statusCounts->sum(),
            'total_sent' => $sent,
            'failed' => $failed,
            'pending' => $this->statusCount($statusCounts, EmailDeliveryStatus::PENDING),
            'success_rate' => $totalCompleted > 0
                ? (int) round(($sent / $totalCompleted) * 100)
                : 0,
            'last_execution' => $this->lastExecution($automation),
            'next_execution' => $this->nextExecution($automation),
            'customers_matching' => $this->matcher->query($automation)->count(),
        ];
    }

    private function dailyChartData(Automation $automation): array
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $queued = $this->dailyCounts(
            $automation,
            'created_at',
            $start,
            $end,
        );

        $sent = $this->dailyCounts(
            $automation,
            'sent_at',
            $start,
            $end,
            fn ($query) => $query->where('status', EmailDeliveryStatus::SENT->value),
        );

        $failed = $this->dailyCounts(
            $automation,
            'failed_at',
            $start,
            $end,
            fn ($query) => $query->whereIn('status', [
                EmailDeliveryStatus::FAILED->value,
                EmailDeliveryStatus::BOUNCED->value,
            ]),
        );

        $rows = collect(range(0, 29))
            ->map(function (int $offset) use ($start, $queued, $sent, $failed): array {
                $date = $start->copy()->addDays($offset)->toDateString();

                return [
                    'date' => $date,
                    'queued' => (int) ($queued[$date] ?? 0),
                    'sent' => (int) ($sent[$date] ?? 0),
                    'failed' => (int) ($failed[$date] ?? 0),
                ];
            })
            ->all();

        return [
            'rows' => $rows,
            'labels' => collect($rows)
                ->map(fn (array $row): string => Carbon::parse($row['date'])->format('M j'))
                ->all(),
            'series' => [
                [
                    'name' => __('Queued'),
                    'data' => collect($rows)->pluck('queued')->all(),
                ],
                [
                    'name' => __('Sent'),
                    'data' => collect($rows)->pluck('sent')->all(),
                ],
                [
                    'name' => __('Failed'),
                    'data' => collect($rows)->pluck('failed')->all(),
                ],
            ],
        ];
    }

    private function dailyCounts(
        Automation $automation,
        string $dateColumn,
        CarbonInterface $start,
        CarbonInterface $end,
        ?callable $scope = null,
    ): Collection {
        $query = EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->whereNotNull($dateColumn)
            ->whereBetween($dateColumn, [$start, $end]);

        if ($scope !== null) {
            $scope($query);
        }

        return $query
            ->selectRaw("DATE({$dateColumn}) as delivery_date, count(*) as aggregate")
            ->groupByRaw("DATE({$dateColumn})")
            ->pluck('aggregate', 'delivery_date');
    }

    private function lastExecution(Automation $automation): ?string
    {
        $date = EmailDelivery::query()
            ->where('automation_id', $automation->id)
            ->latest('created_at')
            ->value('created_at');

        return $date
            ? Carbon::parse($date)->format('Y-m-d H:i')
            : null;
    }

    private function nextExecution(Automation $automation): ?string
    {
        if (! $automation->is_active || ! $automation->schedule) {
            return null;
        }

        $next = match ($automation->schedule_type) {
            ScheduleType::ONCE => $automation->schedule->run_date
                ? $this->withSendTime($automation, $automation->schedule->run_date->copy())
                : null,
            ScheduleType::AFTER_SIGNUP,
            ScheduleType::DAILY => $this->nextDailyExecution($automation),
            ScheduleType::WEEKLY => $this->nextWeeklyExecution($automation),
            ScheduleType::MONTHLY => $this->nextMonthlyExecution($automation),
        };

        if (! $next || $next->lessThanOrEqualTo(now())) {
            return null;
        }

        return $next->format('Y-m-d H:i');
    }

    private function nextDailyExecution(Automation $automation): CarbonInterface
    {
        $next = $this->withSendTime($automation, now());

        return $next->lessThanOrEqualTo(now())
            ? $next->addDay()
            : $next;
    }

    private function nextWeeklyExecution(Automation $automation): CarbonInterface
    {
        $weekday = $automation->schedule?->weekday ?? now()->dayOfWeek;
        $daysUntil = ($weekday - now()->dayOfWeek + 7) % 7;
        $next = $this->withSendTime($automation, now()->addDays($daysUntil));

        return $next->lessThanOrEqualTo(now())
            ? $next->addWeek()
            : $next;
    }

    private function nextMonthlyExecution(Automation $automation): CarbonInterface
    {
        $next = $this->monthlyExecutionFor(now(), $automation);

        return $next->lessThanOrEqualTo(now())
            ? $this->monthlyExecutionFor(now()->addMonthNoOverflow(), $automation)
            : $next;
    }

    private function monthlyExecutionFor(CarbonInterface $date, Automation $automation): CarbonInterface
    {
        $month = Carbon::parse($date)->startOfMonth();
        $day = min($automation->schedule?->month_day ?? 1, $month->daysInMonth);

        return $this->withSendTime($automation, $month->day($day));
    }

    private function withSendTime(Automation $automation, CarbonInterface $date): CarbonInterface
    {
        $sendTime = $automation->schedule?->send_time;

        if (! $sendTime instanceof CarbonInterface) {
            return Carbon::parse($date)->startOfDay();
        }

        return Carbon::parse($date)->setTime(
            hour: (int) $sendTime->format('H'),
            minute: (int) $sendTime->format('i'),
        );
    }

    private function statusCount(Collection $counts, EmailDeliveryStatus $status): int
    {
        return (int) ($counts[$status->value] ?? 0);
    }

    private function cacheKey(Automation $automation): string
    {
        return "automation.{$automation->id}.statistics.v1";
    }
}
