<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryBatchStatus;
use App\Enums\EmailDeliveryStatus;
use App\Jobs\SendEmailBatchJob;
use App\Models\EmailDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryActionService
{
    public function __construct(
        private DeliveryQuery $deliveryQuery,
        private EmailBatchDispatcher $dispatcher,
    ) {}

    public function retry(EmailDelivery $delivery): void
    {
        if ($delivery->status !== EmailDeliveryStatus::FAILED) {
            throw ValidationException::withMessages([
                'delivery' => __('Only failed deliveries can be retried.'),
            ]);
        }

        if ($delivery->email_delivery_batch_id !== null) {
            SendEmailBatchJob::dispatch($delivery->email_delivery_batch_id);

            return;
        }

        $delivery->update([
            'status' => EmailDeliveryStatus::PENDING,
            'processing_at' => null,
            'sent_at' => null,
            'failed_at' => null,
            'provider_message_id' => null,
            'error_message' => null,
        ]);
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

    /**
     * @return array{queued: int, skipped: int, deferred: int}
     */
    public function retryFilteredFailures(array $filters): array
    {
        $requested = $this->deliveryQuery->failed($filters)->count();

        if ($requested === 0) {
            return ['queued' => 0, 'skipped' => 0, 'deferred' => 0];
        }

        $claimedIds = DB::transaction(function () use ($filters) {
            $deliveries = $this->deliveryQuery->failed($filters)
                ->where(function ($query): void {
                    $query->whereNull('email_delivery_batch_id')
                        ->orWhereHas(
                            'batch',
                            fn ($batch) => $batch->where('status', EmailDeliveryBatchStatus::FAILED->value)
                        );
                })
                ->reorder('email_deliveries.id')
                ->lockForUpdate()
                ->get(['email_deliveries.id']);

            if ($deliveries->isEmpty()) {
                return $deliveries->pluck('id');
            }

            $ids = $deliveries->pluck('id');

            EmailDelivery::query()
                ->whereKey($ids)
                ->where('status', EmailDeliveryStatus::FAILED->value)
                ->update([
                    'email_delivery_batch_id' => null,
                    'status' => EmailDeliveryStatus::PENDING->value,
                    'processing_at' => null,
                    'sent_at' => null,
                    'failed_at' => null,
                    'provider_message_id' => null,
                    'error_message' => null,
                ]);

            return $ids;
        }, 3);

        $claimed = $claimedIds->count();

        if ($claimed > 0) {
            // One delivery per batch is the upper bound when filters span many
            // automation/template groups. The dispatcher stops at daily quota.
            $this->dispatcher->dispatchDue($claimed);
        }

        $queued = EmailDelivery::query()
            ->whereKey($claimedIds)
            ->whereNotNull('email_delivery_batch_id')
            ->count();

        return [
            'queued' => $queued,
            'skipped' => max(0, $requested - $claimed),
            'deferred' => $claimed - $queued,
        ];
    }
}
