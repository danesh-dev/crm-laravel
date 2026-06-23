<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryStatus;
use App\Models\EmailDelivery;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DeliveryService
{
    public function send(EmailDelivery $delivery): void
    {
        try {

            $delivery->update([
                'status' => EmailDeliveryStatus::PROCESSING,
                'processing_at' => now(),
            ]);

            //TODO: email sending here later

            $delivery->update([
                'status' => EmailDeliveryStatus::SENT,
                'sent_at' => now(),
            ]);

        } catch (Throwable $e) {

            $delivery->update([
                'status' => EmailDeliveryStatus::FAILED,
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}