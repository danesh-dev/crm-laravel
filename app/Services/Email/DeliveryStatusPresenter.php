<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryStatus;

class DeliveryStatusPresenter
{
    public function badgeVariant(EmailDeliveryStatus $status): string
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
}
