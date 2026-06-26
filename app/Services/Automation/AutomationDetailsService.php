<?php

namespace App\Services\Automation;

use App\Enums\EmailDeliveryStatus;
use App\Models\Automation;
use App\Models\EmailDelivery;
use Illuminate\Support\Collection;

class AutomationDetailsService
{
    public function __construct(
        private AutomationMatcher $matcher,
    ) {}

    public function statistics(Automation $automation): array
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
            'customers_matched' => $this->matcher->query($automation)->count(),
            'emails_queued' => (int) $statusCounts->sum(),
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $this->statusCount($statusCounts, EmailDeliveryStatus::PENDING),
            'success_rate' => $totalCompleted > 0
                ? round(($sent / $totalCompleted) * 100)
                : 0,
        ];
    }

    public function recentDeliveries(Automation $automation): Collection
    {
        return EmailDelivery::query()
            ->with([
                'customer:id,name,email',
                'emailTemplate:id,name',
            ])
            ->where('automation_id', $automation->id)
            ->latest()
            ->limit(20)
            ->get([
                'id',
                'automation_id',
                'customer_id',
                'email_template_id',
                'status',
                'scheduled_for',
                'sent_at',
                'failed_at',
                'created_at',
            ]);
    }

    private function statusCount(Collection $counts, EmailDeliveryStatus $status): int
    {
        return (int) ($counts[$status->value] ?? 0);
    }
}
