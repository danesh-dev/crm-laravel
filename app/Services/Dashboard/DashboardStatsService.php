<?php

namespace App\Services\Dashboard;

use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;
use App\Models\Subscription;

class DashboardStatsService
{
    public function get(): array
    {
        $totalDeliveriesToday = EmailDelivery::query()
            ->whereDate('created_at', today())
            ->count();

        $emailsSentToday = EmailDelivery::query()
            ->whereDate('sent_at', today())
            ->count();

        $emailsFailedToday = EmailDelivery::query()
            ->whereDate('failed_at', today())
            ->count();

        return [

            'customers' => Customer::count(),

            'active_subscribers' => Subscription::active()
                ->distinct('customer_id')
                ->count(),

            'automations' => Automation::active()->count(),

            'emails_sent_today' => $emailsSentToday,

            'emails_failed_today' => $emailsFailedToday,

            'emails_pending' => EmailDelivery::query()
                ->pending()
                ->count(),

            'delivery_success_rate' => $totalDeliveriesToday > 0
                ? round(($emailsSentToday / $totalDeliveriesToday) * 100)
                : 0,

            'recent_deliveries' => EmailDelivery::query()
                ->with([
                    'customer',
                    'automation',
                    'emailTemplate',
                ])
                ->latest()
                ->limit(6)
                ->get(),
        ];
    }
}
