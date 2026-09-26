<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryBatchStatus;
use App\Enums\EmailDeliveryStatus;
use App\Models\EmailDelivery;
use App\Models\EmailDeliveryBatch;
use App\Models\MailDailyQuota;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class MailQuotaService
{
    public function sentToday(): int
    {
        return (int) (MailDailyQuota::query()
            ->where('quota_date', $this->today())
            ->value('sent_messages') ?? 0);
    }

    public function remainingToday(): int
    {
        $quota = MailDailyQuota::query()
            ->where('quota_date', $this->today())
            ->first();

        return max(0, $this->dailyLimit()
            - (int) ($quota?->sent_messages ?? 0)
            - (int) ($quota?->reserved_messages ?? 0));
    }

    public function canReserve(int $messageCount = 1): bool
    {
        return $messageCount > 0 && $messageCount <= $this->remainingToday();
    }

    public function reserveNextBatch(): ?EmailDeliveryBatch
    {
        return DB::transaction(function (): ?EmailDeliveryBatch {
            $quota = $this->lockedQuota($this->today());
            $remaining = $this->remainingFor($quota);

            if ($remaining === 0) {
                return null;
            }

            $now = $this->now();
            $first = EmailDelivery::query()
                ->pending()
                ->due()
                ->whereHas('customer', fn ($query) => $query->whereNotNull('email'))
                ->orderBy('scheduled_for')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($first === null) {
                return null;
            }

            $deliveryIds = EmailDelivery::query()
                ->pending()
                ->due()
                ->where('automation_id', $first->automation_id)
                ->where('email_template_id', $first->email_template_id)
                ->whereHas('customer', fn ($query) => $query->whereNotNull('email'))
                ->orderBy('scheduled_for')
                ->orderBy('id')
                ->limit($this->batchSize())
                ->lockForUpdate()
                ->pluck('id');

            if ($deliveryIds->isEmpty()) {
                return null;
            }

            $availableAt = $quota->next_send_at?->isAfter($now)
                ? $quota->next_send_at->copy()
                : $now->copy();

            $batch = EmailDeliveryBatch::query()->create([
                'automation_id' => $first->automation_id,
                'email_template_id' => $first->email_template_id,
                'quota_date' => $this->today(),
                'scheduled_for' => $first->scheduled_for,
                'available_at' => $availableAt,
                'recipient_count' => $deliveryIds->count(),
                'status' => EmailDeliveryBatchStatus::QUEUED,
            ]);

            EmailDelivery::query()
                ->whereKey($deliveryIds)
                ->where('status', EmailDeliveryStatus::PENDING->value)
                ->update([
                    'email_delivery_batch_id' => $batch->id,
                    'status' => EmailDeliveryStatus::PROCESSING,
                    'processing_at' => $now,
                    'failed_at' => null,
                    'error_message' => null,
                ]);

            $quota->update([
                'reserved_messages' => $quota->reserved_messages + 1,
                'reserved_recipients' => $quota->reserved_recipients + $deliveryIds->count(),
                'next_send_at' => $availableAt->copy()->addSeconds($this->delaySeconds()),
            ]);

            return $batch;
        }, 3);
    }

    public function complete(EmailDeliveryBatch $batch): void
    {
        DB::transaction(function () use ($batch): void {
            $batch = EmailDeliveryBatch::query()->lockForUpdate()->findOrFail($batch->id);

            if ($batch->status !== EmailDeliveryBatchStatus::SENDING) {
                return;
            }

            $quota = $this->lockedQuota($batch->quota_date->toDateString());
            $now = $this->now();

            $quota->update([
                'reserved_messages' => max(0, $quota->reserved_messages - 1),
                'sent_messages' => $quota->sent_messages + 1,
                'reserved_recipients' => max(0, $quota->reserved_recipients - $batch->recipient_count),
                'sent_recipients' => $quota->sent_recipients + $batch->recipient_count,
            ]);

            $batch->update([
                'status' => EmailDeliveryBatchStatus::SENT,
                'sent_at' => $now,
            ]);

            $batch->deliveries()->update([
                'status' => EmailDeliveryStatus::SENT,
                'sent_at' => $now,
                'failed_at' => null,
                'error_message' => null,
            ]);
        }, 3);
    }

    public function release(EmailDeliveryBatch $batch, string $error): void
    {
        DB::transaction(function () use ($batch, $error): void {
            $batch = EmailDeliveryBatch::query()->lockForUpdate()->findOrFail($batch->id);

            if (! in_array($batch->status, [
                EmailDeliveryBatchStatus::QUEUED,
                EmailDeliveryBatchStatus::PROCESSING,
                EmailDeliveryBatchStatus::SENDING,
            ], true)) {
                return;
            }

            $quota = $this->lockedQuota($batch->quota_date->toDateString());
            $now = $this->now();

            $quota->update([
                'reserved_messages' => max(0, $quota->reserved_messages - 1),
                'reserved_recipients' => max(0, $quota->reserved_recipients - $batch->recipient_count),
            ]);

            $batch->update([
                'status' => EmailDeliveryBatchStatus::FAILED,
                'failed_at' => $now,
                'error_message' => $error,
            ]);

            $batch->deliveries()->update([
                'status' => EmailDeliveryStatus::FAILED,
                'failed_at' => $now,
                'error_message' => $error,
            ]);
        }, 3);
    }

    private function lockedQuota(string $date): MailDailyQuota
    {
        MailDailyQuota::query()->insertOrIgnore([
            'quota_date' => $date,
            'sent_messages' => 0,
            'reserved_messages' => 0,
            'sent_recipients' => 0,
            'reserved_recipients' => 0,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);

        return MailDailyQuota::query()
            ->where('quota_date', $date)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function remainingFor(MailDailyQuota $quota): int
    {
        return max(0, $this->dailyLimit() - $quota->sent_messages - $quota->reserved_messages);
    }

    private function dailyLimit(): int
    {
        return max(0, (int) config('mail.automation.daily_limit', 500));
    }

    private function batchSize(): int
    {
        return min(100, max(1, (int) config('mail.automation.bcc_batch_size', 100)));
    }

    private function delaySeconds(): int
    {
        return max(0, (int) config('mail.automation.bcc_delay_seconds', 5));
    }

    private function today(): string
    {
        return $this->now()->toDateString();
    }

    private function now(): CarbonInterface
    {
        return now(config('app.timezone'));
    }
}
