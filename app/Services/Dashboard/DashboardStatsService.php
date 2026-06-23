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
        return [

            'customers' => Customer::count(),

            'active_subscribers' => Subscription::active()
                ->distinct('customer_id')
                ->count(),

            'automations' => Automation::active()->count(),

            'emails_sent_today' => EmailDelivery::query()
                ->whereDate('sent_at', today())
                ->count(),

            'emails_failed_today' => EmailDelivery::query()
                ->whereDate('failed_at', today())
                ->count(),
        ];
    }
}