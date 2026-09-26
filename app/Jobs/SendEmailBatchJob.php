<?php

namespace App\Jobs;

use App\Enums\EmailDeliveryBatchStatus;
use App\Models\EmailDeliveryBatch;
use App\Services\Email\DeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailBatchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(public int $batchId) {}

    public function handle(DeliveryService $deliveryService): void
    {
        $batch = EmailDeliveryBatch::query()->findOrFail($this->batchId);

        if (in_array($batch->status, [
            EmailDeliveryBatchStatus::SENT,
            EmailDeliveryBatchStatus::SENDING,
            EmailDeliveryBatchStatus::FAILED,
        ], true)) {
            return;
        }

        if (
            $batch->status === EmailDeliveryBatchStatus::PROCESSING
            && $batch->processing_at?->isAfter(now()->subSeconds(75))
        ) {
            $this->release(60);

            return;
        }

        $deliveryService->sendBatch($batch->fresh());
    }
}
