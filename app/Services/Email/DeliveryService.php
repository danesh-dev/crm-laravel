<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryBatchStatus;
use App\Mail\AutomationMail;
use App\Models\EmailDeliveryBatch;
use App\Services\Settings\MailSettingsService;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DeliveryService
{
    public function __construct(
        protected TemplateRenderer $renderer,
        protected MailSettingsService $mailSettings,
        protected MailQuotaService $quota,
    ) {}

    public function sendBatch(EmailDeliveryBatch $batch): void
    {
        $now = now(config('app.timezone'));
        $smtpAccepted = false;

        $claimed = EmailDeliveryBatch::query()
            ->whereKey($batch->id)
            ->where(function ($query) use ($now): void {
                $query->where('status', EmailDeliveryBatchStatus::QUEUED->value)
                    ->orWhere(function ($query) use ($now): void {
                        $query->where('status', EmailDeliveryBatchStatus::PROCESSING->value)
                            ->where('processing_at', '<=', $now->copy()->subSeconds(75));
                    });
            })
            ->where('scheduled_for', '<=', $now)
            ->update([
                'status' => EmailDeliveryBatchStatus::PROCESSING,
                'processing_at' => $now,
            ]);

        if ($claimed === 0) {
            return;
        }

        try {

            $batch->refresh()->load(['emailTemplate', 'deliveries.customer:id,email']);
            $rendered = $this->renderer->renderTemplate($batch->emailTemplate);
            $recipients = $batch->deliveries
                ->pluck('customer.email')
                ->filter()
                ->values()
                ->all();

            if (count($recipients) !== $batch->recipient_count) {
                throw new \RuntimeException('The batch recipient list changed after quota was reserved.');
            }

            $this->mailSettings->refresh();

            $sending = EmailDeliveryBatch::query()
                ->whereKey($batch->id)
                ->where('status', EmailDeliveryBatchStatus::PROCESSING->value)
                ->update([
                    'status' => EmailDeliveryBatchStatus::SENDING,
                    'sending_at' => now(config('app.timezone')),
                ]);

            if ($sending === 0) {
                return;
            }

            EmailDeliveryBatch::query()
                ->whereKey($batch->id)
                ->where('status', EmailDeliveryBatchStatus::SENDING->value)
                ->update([
                    'attempt_count' => $batch->attempt_count + 1,
                    'last_attempted_at' => now(config('app.timezone')),
                ]);

            Mail::to((string) config('mail.from.address'))
                ->bcc($recipients)
                ->send(
                    new AutomationMail(
                        $rendered['subject'],
                        $rendered['body']
                    )
                );

            $smtpAccepted = true;

            $this->quota->complete($batch);

        } catch (Throwable $e) {
            if (! $smtpAccepted) {
                $this->quota->release($batch, $e->getMessage());
            }

            throw $e;
        }
    }
}
