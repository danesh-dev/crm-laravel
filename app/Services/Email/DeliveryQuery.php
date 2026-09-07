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
                'customer:id,name,email,phone,source,signup_at,last_login_at',
                'automation:id,name',
                'emailTemplate:id,name,subject',
                'batch:id,recipient_count,status',
            ])
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['automation_id'] ?? null,
                fn ($query, $automationId) => $query->where('automation_id', $automationId)
            )
            ->when(
                $filters['email_template_id'] ?? null,
                fn ($query, $templateId) => $query->where('email_template_id', $templateId)
            )
            ->when(
                $filters['date_from'] ?? null,
                fn ($query, $dateFrom) => $query->whereDate('scheduled_for', '>=', $dateFrom)
            )
            ->when(
                $filters['date_to'] ?? null,
                fn ($query, $dateTo) => $query->whereDate('scheduled_for', '<=', $dateTo)
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search): void {
                    $query->whereHas(
                        'customer',
                        fn ($customer) => $customer
                            ->where('name', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%")
                    );
                }
            )
            ->latest();
    }
}
