<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryStatus;
use App\Jobs\SendEmailDeliveryJob;
use App\Models\EmailDelivery;
use Illuminate\Validation\ValidationException;

class DeliveryActionService
{
    public function retry(EmailDelivery $delivery): void
    {
        if ($delivery->status !== EmailDeliveryStatus::FAILED) {
            throw ValidationException::withMessages([
                'delivery' => __('Only failed deliveries can be retried.'),
            ]);
        }

        $delivery->update([
            'status' => EmailDeliveryStatus::PENDING,
            'processing_at' => null,
            'sent_at' => null,
            'failed_at' => null,
            'provider_message_id' => null,
            'error_message' => null,
        ]);

        SendEmailDeliveryJob::dispatch($delivery->id);
    }

    public function cancel(EmailDelivery $delivery): void
    {
        if ($delivery->status !== EmailDeliveryStatus::PENDING) {
            throw ValidationException::withMessages([
                'delivery' => __('Only pending deliveries can be cancelled.'),
            ]);
        }

        $delivery->update([
            'status' => EmailDeliveryStatus::CANCELLED,
        ]);
    }
}
