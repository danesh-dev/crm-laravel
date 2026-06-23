<?php

namespace App\Services\Email;

use App\Enums\EmailDeliveryStatus;
use App\Mail\AutomationMail;
use App\Models\EmailDelivery;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DeliveryService
{
    public function __construct(
        protected TemplateRenderer $renderer
    ) {}

    public function send(EmailDelivery $delivery): void
    {
        try {

            $delivery->update([
                'status' => EmailDeliveryStatus::PROCESSING,
                'processing_at' => now(),
            ]);

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
