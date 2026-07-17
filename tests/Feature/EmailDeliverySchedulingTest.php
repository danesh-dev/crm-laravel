<?php

namespace Tests\Feature;

use App\Enums\EmailDeliveryStatus;
use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use App\Enums\UserSource;
use App\Jobs\SendEmailDeliveryJob;
use App\Mail\AutomationMail;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Email\DeliveryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailDeliverySchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_future_scheduled_delivery_is_not_processed(): void
    {
        Mail::fake();

        $timezone = config('app.timezone');
        Carbon::setTestNow(Carbon::create(2026, 7, 17, 0, 0, 0, $timezone));

        $delivery = $this->createDelivery(
            scheduledFor: Carbon::create(2026, 7, 17, 9, 0, 0, $timezone)
        );

        (new SendEmailDeliveryJob($delivery->id))
            ->handle(app(DeliveryService::class));

        Mail::assertNothingSent();

        $this->assertDatabaseHas('email_deliveries', [
            'id' => $delivery->id,
            'status' => EmailDeliveryStatus::PENDING->value,
            'processing_at' => null,
            'sent_at' => null,
        ]);
    }

    public function test_due_scheduled_delivery_is_processed(): void
    {
        Mail::fake();

        $timezone = config('app.timezone');
        Carbon::setTestNow(Carbon::create(2026, 7, 17, 9, 1, 0, $timezone));

        $delivery = $this->createDelivery(
            scheduledFor: Carbon::create(2026, 7, 17, 9, 0, 0, $timezone)
        );

        (new SendEmailDeliveryJob($delivery->id))
            ->handle(app(DeliveryService::class));

        Mail::assertSent(AutomationMail::class);

        $this->assertDatabaseHas('email_deliveries', [
            'id' => $delivery->id,
            'status' => EmailDeliveryStatus::SENT->value,
        ]);

        $this->assertNotNull($delivery->fresh()->sent_at);
    }

    private function createDelivery(Carbon $scheduledFor): EmailDelivery
    {
        $user = User::factory()->create();

        $template = EmailTemplate::query()->create([
            'name' => 'Welcome',
            'subject' => 'Hello {{name}}',
            'body_html' => '<p>Hello {{email}}</p>',
            'created_by' => $user->id,
        ]);

        $automation = Automation::query()->create([
            'name' => 'Daily test',
            'target_user_type' => TargetUserType::ALL,
            'schedule_type' => ScheduleType::DAILY,
            'is_active' => true,
            'email_template_id' => $template->id,
            'created_by' => $user->id,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'source' => UserSource::MANUAL,
            'signup_at' => now(config('app.timezone')),
        ]);

        return EmailDelivery::query()->create([
            'automation_id' => $automation->id,
            'customer_id' => $customer->id,
            'email_template_id' => $template->id,
            'status' => EmailDeliveryStatus::PENDING,
            'scheduled_for' => $scheduledFor,
        ]);
    }
}
