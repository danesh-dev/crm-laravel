<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryBatchStatus;
use App\Jobs\SendEmailBatchJob;
use App\Models\EmailDeliveryBatch;

class EmailBatchDispatcher
{
    public function __construct(private MailQuotaService $quota) {}

    public function dispatchDue(int $maxBatches = 100): int
    {
        $dispatched = 0;
        $maxBatches = max(1, $maxBatches);

        EmailDeliveryBatch::query()
            ->where('status', EmailDeliveryBatchStatus::QUEUED)
            ->whereNull('dispatched_at')
            ->orderBy('id')
            ->limit($maxBatches)
            ->get()
            ->each(function (EmailDeliveryBatch $batch) use (&$dispatched): void {
                $this->dispatch($batch);
                $dispatched++;
            });

        while ($dispatched < $maxBatches) {
            $batch = $this->quota->reserveNextBatch();

            if ($batch === null) {
                break;
            }

            $this->dispatch($batch);
            $dispatched++;
        }

        return $dispatched;
    }

    private function dispatch(EmailDeliveryBatch $batch): void
    {
        SendEmailBatchJob::dispatch($batch->id)->delay($batch->available_at);
        $batch->update(['dispatched_at' => now(config('app.timezone'))]);
    }
}
