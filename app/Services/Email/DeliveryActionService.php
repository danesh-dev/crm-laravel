<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryBatchStatus;
use App\Enums\EmailDeliveryStatus;
use App\Models\EmailDelivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryActionService
{
    public function __construct(
        private DeliveryQuery $deliveryQuery,
    ) {}

    public function retry(EmailDelivery $delivery): void
    {
        $retried = DB::transaction(function () use ($delivery): bool {
            $delivery = EmailDelivery::query()
                ->lockForUpdate()
                ->findOrFail($delivery->id);

            if ($delivery->status !== EmailDeliveryStatus::FAILED) {
                return false;
            }

            if (
                $delivery->email_delivery_batch_id !== null
                && $delivery->batch()->lockForUpdate()->first()?->status !== EmailDeliveryBatchStatus::FAILED
            ) {
                return false;
            }

            $delivery->update($this->retryAttributes());

            return true;
        }, 3);

        if (! $retried) {
            throw ValidationException::withMessages([
                'delivery' => __('Only failed deliveries can be retried.'),
            ]);
        }
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
                ->update($this->retryAttributes());

            return $ids;
        }, 3);

        $claimed = $claimedIds->count();

        return [
            'queued' => $claimed,
            'skipped' => max(0, $requested - $claimed),
            'deferred' => 0,
        ];
    }

    private function retryAttributes(): array
    {
        return [
            'email_delivery_batch_id' => null,
            'status' => EmailDeliveryStatus::PENDING->value,
            'scheduled_for' => now(config('app.timezone'))->addSeconds(
                max(1, (int) config('mail.automation.retry_delay_seconds', 60))
            ),
            'processing_at' => null,
            'sent_at' => null,
            'failed_at' => null,
            'provider_message_id' => null,
            'error_message' => null,
        ];
    }
}
