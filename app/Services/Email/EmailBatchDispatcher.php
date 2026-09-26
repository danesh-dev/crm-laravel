<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryBatchStatus;
use App\Jobs\SendEmailBatchJob;
use App\Models\EmailDeliveryBatch;
use Throwable;

class EmailBatchDispatcher
{
    public function __construct(private MailQuotaService $quota) {}

    public function dispatchDue(int $maxBatches = 100): int
    {
        $dispatched = 0;
        $maxBatches = max(1, $maxBatches);

        EmailDeliveryBatch::query()
            ->where(function ($query): void {
                $query->where(function ($query): void {
                    $query->where('status', EmailDeliveryBatchStatus::QUEUED)
                        ->whereNull('dispatched_at');
                })->orWhere(function ($query): void {
                    $query->whereIn('status', [
                        EmailDeliveryBatchStatus::QUEUED,
                        EmailDeliveryBatchStatus::PROCESSING,
                    ])->where('available_at', '<=', now(config('app.timezone')))
                        ->where('dispatched_at', '<=', now(config('app.timezone'))->subMinutes(5));
                });
            })
            ->orderBy('id')
            ->limit($maxBatches)
            ->get()
            ->each(function (EmailDeliveryBatch $batch) use (&$dispatched): void {
                if ($this->dispatch($batch)) {
                    $dispatched++;
                }
            });

        while ($dispatched < $maxBatches) {
            $batch = $this->quota->reserveNextBatch();

            if ($batch === null) {
                break;
            }

            if ($this->dispatch($batch)) {
                $dispatched++;
            }
        }

        return $dispatched;
    }

    private function dispatch(EmailDeliveryBatch $batch): bool
    {
        $now = now(config('app.timezone'));
        $claimed = EmailDeliveryBatch::query()
            ->whereKey($batch->id)
            ->whereIn('status', [
                EmailDeliveryBatchStatus::QUEUED->value,
                EmailDeliveryBatchStatus::PROCESSING->value,
            ])
            ->where(function ($query) use ($now): void {
                $query->whereNull('dispatched_at')
                    ->orWhere('dispatched_at', '<=', $now->copy()->subMinutes(5));
            })
            ->update(['dispatched_at' => $now]);

        if ($claimed === 0) {
            return false;
        }

        try {
            SendEmailBatchJob::dispatch($batch->id)->delay($batch->available_at);
        } catch (Throwable $exception) {
            EmailDeliveryBatch::query()
                ->whereKey($batch->id)
                ->where('dispatched_at', $now)
                ->update(['dispatched_at' => null]);

            throw $exception;
        }

        return true;
    }
}
