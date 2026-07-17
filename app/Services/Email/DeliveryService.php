<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryStatus;
use App\Mail\AutomationMail;
use App\Models\EmailDelivery;
use App\Services\Settings\MailSettingsService;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DeliveryService
{
    public function __construct(
        protected TemplateRenderer $renderer,
        protected MailSettingsService $mailSettings,
    ) {}

    public function send(EmailDelivery $delivery): void
    {
        $now = now(config('app.timezone'));

        $claimed = EmailDelivery::query()
            ->whereKey($delivery->id)
            ->where('status', EmailDeliveryStatus::PENDING->value)
            ->where('scheduled_for', '<=', $now)
            ->update([
                'status' => EmailDeliveryStatus::PROCESSING,
                'processing_at' => $now,
            ]);

        if ($claimed === 0) {
            return;
        }

        try {

            $delivery->refresh();

            $customer = $delivery->customer;
            $template = $delivery->emailTemplate;

            $subject = $this->renderer->render(
                $template->subject,
                $customer
            );

            $body = $this->renderer->render(
                $template->body_html,
                $customer
            );

            $this->mailSettings->refresh();

            Mail::to($customer->email)
                ->send(
                    new AutomationMail(
                        $subject,
                        $body
                    )
                );

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
