<?php

namespace App\Services\Automation;

use App\Models\Automation;
use App\Models\EmailDelivery;
use Illuminate\Support\Collection;

class AutomationDetailsService
{
    public function recentDeliveries(Automation $automation): Collection
    {
        return EmailDelivery::query()
            ->with([
                'customer:id,name,email',
                'emailTemplate:id,name',
                'batch:id,recipient_count,status',
            ])
            ->where('automation_id', $automation->id)
            ->latest()
            ->limit(20)
            ->get([
                'id',
                'automation_id',
                'customer_id',
                'email_template_id',
                'email_delivery_batch_id',
                'status',
                'scheduled_for',
                'sent_at',
                'failed_at',
                'created_at',
            ]);
    }
}
