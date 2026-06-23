<?php

namespace App\Jobs;

use App\Models\EmailDelivery;
use App\Services\Email\DeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailDeliveryJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $deliveryId
    ) {}

    public function handle(
        DeliveryService $deliveryService
    ): void {

        $delivery = EmailDelivery::query()
            ->findOrFail($this->deliveryId);

        $deliveryService->send($delivery);
    }
}