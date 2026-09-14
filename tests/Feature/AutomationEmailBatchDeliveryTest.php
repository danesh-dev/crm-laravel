<?php

namespace Tests\Feature;

use App\Enums\EmailDeliveryBatchStatus;
use App\Enums\EmailDeliveryStatus;
use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use App\Enums\UserSource;
use App\Jobs\SendEmailBatchJob;
use App\Livewire\Deliveries\Index as DeliveriesIndex;
use App\Mail\AutomationMail;
use App\Models\Automation;
use App\Models\Customer;
use App\Models\EmailDelivery;
use App\Models\EmailDeliveryBatch;
use App\Models\EmailTemplate;
use App\Models\MailDailyQuota;
use App\Models\User;
use App\Services\Automation\AutomationEngine;
use App\Services\Email\DeliveryActionService;
use App\Services\Email\DeliveryService;
use App\Services\Email\EmailBatchDispatcher;
use App\Services\Email\MailQuotaService;
use App\Services\Email\TemplateRenderer;
use App\Services\Settings\MailSettingsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Livewire\Livewire;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AutomationEmailBatchDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'queue.default' => 'sync',
            'mail.automation.daily_limit' => 500,
            'mail.automation.bcc_batch_size' => 100,
            'mail.automation.bcc_delay_seconds' => 5,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 9, 7, 10, 0, 0, config('app.timezone')));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_50_recipients_are_sent_in_one_smtp_message(): void
    {
        $this->assertBatchDelivery(50, [50]);
    }

    public function test_100_recipients_are_sent_in_one_smtp_message(): void
    {
        $this->assertBatchDelivery(100, [100]);
    }

    public function test_250_recipients_are_sent_as_100_100_and_50(): void
    {
        $this->assertBatchDelivery(250, [100, 100, 50]);
    }

    public function test_500_recipients_use_exactly_the_daily_limit(): void
    {
        $this->assertBatchDelivery(500, [100, 100, 100, 100, 100]);
        $this->assertSame(0, app(MailQuotaService::class)->remainingToday());
    }

    public function test_600_recipients_leave_100_pending_after_daily_limit(): void
    {
        Mail::fake();
        $this->createDeliveries(600);

        app(EmailBatchDispatcher::class)->dispatchDue(20);

        Mail::assertSent(AutomationMail::class, 5);
        $this->assertSame(500, EmailDelivery::query()->where('status', EmailDeliveryStatus::SENT)->count());
        $this->assertSame(100, EmailDelivery::query()->where('status', EmailDeliveryStatus::PENDING)->count());
        $this->assertSame(500, app(MailQuotaService::class)->sentToday());
    }

    public function test_only_50_recipients_are_claimed_when_450_were_already_sent(): void
    {
        Mail::fake();
        $this->createDeliveries(100);
        MailDailyQuota::query()->create([
            'quota_date' => now()->toDateString(),
            'sent_recipients' => 450,
            'reserved_recipients' => 0,
        ]);

        app(EmailBatchDispatcher::class)->dispatchDue(10);

        Mail::assertSent(AutomationMail::class, function (AutomationMail $mail): bool {
            return count($mail->bcc) === 50;
        });
        $this->assertSame(50, EmailDelivery::query()->where('status', EmailDeliveryStatus::PENDING)->count());
    }

    public function test_exhausted_quota_sends_nothing(): void
    {
        Mail::fake();
        $this->createDeliveries(10);
        MailDailyQuota::query()->create([
            'quota_date' => now()->toDateString(),
            'sent_recipients' => 500,
            'reserved_recipients' => 0,
        ]);

        $this->assertSame(0, app(EmailBatchDispatcher::class)->dispatchDue());
        Mail::assertNothingSent();
    }

    public function test_daily_limit_can_be_lowered_to_50(): void
    {
        config(['mail.automation.daily_limit' => 50]);
        Mail::fake();
        $this->createDeliveries(100);

        app(EmailBatchDispatcher::class)->dispatchDue(10);

        Mail::assertSent(AutomationMail::class, fn (AutomationMail $mail): bool => count($mail->bcc) === 50);
        $this->assertSame(50, EmailDelivery::query()->where('status', EmailDeliveryStatus::PENDING)->count());
    }

    public function test_atomic_reservations_cannot_exceed_the_limit(): void
    {
        config(['mail.automation.daily_limit' => 150]);
        $this->createDeliveries(300);
        $quota = app(MailQuotaService::class);

        $first = $quota->reserveNextBatch();
        $second = $quota->reserveNextBatch();
        $third = $quota->reserveNextBatch();

        $this->assertSame(100, $first?->recipient_count);
        $this->assertSame(50, $second?->recipient_count);
        $this->assertNull($third);
        $this->assertSame(150, MailDailyQuota::query()->value('reserved_recipients'));
    }

    public function test_batches_receive_configured_throttle_slots(): void
    {
        $this->createDeliveries(250);
        $quota = app(MailQuotaService::class);

        $first = $quota->reserveNextBatch();
        $second = $quota->reserveNextBatch();
        $third = $quota->reserveNextBatch();

        $this->assertSame(now()->timestamp, $first?->available_at->timestamp);
        $this->assertSame(now()->addSeconds(5)->timestamp, $second?->available_at->timestamp);
        $this->assertSame(now()->addSeconds(10)->timestamp, $third?->available_at->timestamp);
    }

    public function test_reserved_batch_with_unconfirmed_queue_dispatch_is_recovered(): void
    {
        Mail::fake();
        $this->createDeliveries(20);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        $this->assertNull($batch?->dispatched_at);

        app(EmailBatchDispatcher::class)->dispatchDue(1);

        Mail::assertSent(AutomationMail::class, 1);
        $this->assertNotNull($batch?->fresh()->dispatched_at);
        $this->assertSame(EmailDeliveryBatchStatus::SENT, $batch?->fresh()->status);
    }

    public function test_template_is_rendered_once_per_batch(): void
    {
        Mail::fake();
        $this->createDeliveries(250);

        $renderer = Mockery::mock(TemplateRenderer::class);
        $renderer->shouldReceive('renderTemplate')
            ->times(3)
            ->andReturn(['subject' => 'Common subject', 'body' => '<p>Common body</p>']);
        $this->app->instance(TemplateRenderer::class, $renderer);

        app(EmailBatchDispatcher::class)->dispatchDue(10);
        Mail::assertSent(AutomationMail::class, 3);
    }

    public function test_customer_variables_are_rejected(): void
    {
        $renderer = app(TemplateRenderer::class);

        $this->expectException(InvalidArgumentException::class);
        $renderer->render('Hello {{ customer.name }}');
    }

    public function test_retry_does_not_resend_a_batch_with_an_ambiguous_smtp_send(): void
    {
        Mail::fake();
        $this->createDeliveries(20);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        $batch->update(['status' => EmailDeliveryBatchStatus::SENDING]);

        (new SendEmailBatchJob($batch->id))->handle(
            app(DeliveryService::class),
            app(MailQuotaService::class),
        );

        Mail::assertNothingSent();
        $this->assertSame(20, EmailDelivery::query()->where('status', EmailDeliveryStatus::PROCESSING)->count());
    }

    public function test_stale_pre_smtp_processing_batch_is_recovered(): void
    {
        Mail::fake();
        $this->createDeliveries(20);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        $batch->update([
            'status' => EmailDeliveryBatchStatus::PROCESSING,
            'processing_at' => now()->subSeconds(76),
        ]);

        (new SendEmailBatchJob($batch->id))->handle(
            app(DeliveryService::class),
            app(MailQuotaService::class),
        );

        Mail::assertSent(AutomationMail::class, 1);
        $this->assertSame(20, EmailDelivery::query()->where('status', EmailDeliveryStatus::SENT)->count());
    }

    public function test_known_failure_retry_succeeds_without_duplicate_deliveries(): void
    {
        Mail::fake();
        $this->createDeliveries(20);
        $quota = app(MailQuotaService::class);
        $batch = $quota->reserveNextBatch();
        $quota->release($batch, 'Known SMTP failure');
        $job = new SendEmailBatchJob($batch->id);

        $job->handle(app(DeliveryService::class), $quota);
        $job->handle(app(DeliveryService::class), $quota);

        Mail::assertSent(AutomationMail::class, 1);
        $this->assertSame(20, EmailDelivery::query()->count());
        $this->assertSame(20, EmailDelivery::query()->where('status', EmailDeliveryStatus::SENT)->count());
    }

    public function test_future_delivery_is_never_claimed_or_sent(): void
    {
        Mail::fake();
        $this->createDeliveries(1, now()->addHour());

        $this->assertSame(0, app(EmailBatchDispatcher::class)->dispatchDue());
        Mail::assertNothingSent();
        $this->assertDatabaseHas('email_deliveries', [
            'status' => EmailDeliveryStatus::PENDING->value,
            'email_delivery_batch_id' => null,
        ]);
    }

    public function test_automation_run_at_midnight_does_not_send_a_09_00_delivery(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2026, 9, 7, 0, 0, 0, config('app.timezone')));
        $user = User::factory()->create();
        $template = EmailTemplate::query()->create([
            'name' => 'Morning message',
            'subject' => 'Common subject',
            'body_html' => '<p>Common body</p>',
            'created_by' => $user->id,
        ]);
        $automation = Automation::query()->create([
            'name' => 'Morning automation',
            'target_user_type' => TargetUserType::ALL,
            'schedule_type' => ScheduleType::DAILY,
            'is_active' => true,
            'email_template_id' => $template->id,
            'created_by' => $user->id,
        ]);
        $automation->schedule()->create(['send_time' => '09:00:00']);
        Customer::query()->create([
            'name' => 'Morning Customer',
            'email' => 'morning@example.com',
            'source' => UserSource::MANUAL,
            'signup_at' => now(),
        ]);

        app(AutomationEngine::class)->run();

        $delivery = EmailDelivery::query()->sole();
        $this->assertSame('2026-09-07 09:00:00', $delivery->scheduled_for->format('Y-m-d H:i:s'));
        $this->assertSame(0, app(EmailBatchDispatcher::class)->dispatchDue());
        Mail::assertNothingSent();
    }

    public function test_failed_smtp_batch_releases_quota_and_marks_deliveries_failed(): void
    {
        $this->createDeliveries(25);
        $batch = app(MailQuotaService::class)->reserveNextBatch();

        $settings = Mockery::mock(MailSettingsService::class);
        $settings->shouldReceive('refresh')->once();
        $this->app->instance(MailSettingsService::class, $settings);
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('SMTP unavailable'));

        try {
            app(DeliveryService::class)->sendBatch($batch);
            $this->fail('The SMTP exception was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('SMTP unavailable', $exception->getMessage());
        }

        $this->assertSame(0, MailDailyQuota::query()->value('reserved_recipients'));
        $this->assertSame(25, EmailDelivery::query()->where('status', EmailDeliveryStatus::FAILED)->count());
        $this->assertSame(EmailDeliveryBatchStatus::FAILED, $batch->fresh()->status);
    }

    public function test_filtered_failures_across_pages_are_rebatched_using_current_size(): void
    {
        Queue::fake();
        $this->createDeliveries(55);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        app(MailQuotaService::class)->release($batch, 'Known SMTP failure');
        config(['mail.automation.bcc_batch_size' => 20]);

        $result = app(DeliveryActionService::class)->retryFilteredFailures($this->emptyFilters());

        $this->assertSame(['queued' => 55, 'skipped' => 0, 'deferred' => 0], $result);
        $this->assertSame([20, 20, 15], EmailDeliveryBatch::query()
            ->whereKeyNot($batch->id)
            ->orderBy('id')
            ->pluck('recipient_count')
            ->all());
        Queue::assertPushed(SendEmailBatchJob::class, 3);
    }

    public function test_bulk_retry_uses_the_same_search_filter_as_the_delivery_list(): void
    {
        Queue::fake();
        $this->createDeliveries(3);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        app(MailQuotaService::class)->release($batch, 'Known SMTP failure');

        $result = app(DeliveryActionService::class)->retryFilteredFailures([
            ...$this->emptyFilters(),
            'search' => 'customer2@example.com',
        ]);

        $this->assertSame(1, $result['queued']);
        $this->assertSame(1, EmailDelivery::query()->where('status', EmailDeliveryStatus::PROCESSING)->count());
        $this->assertSame(2, EmailDelivery::query()->where('status', EmailDeliveryStatus::FAILED)->count());
    }

    public function test_bulk_retry_respects_automation_template_and_date_filters(): void
    {
        Queue::fake();
        $this->createDeliveries(2);
        $firstDelivery = EmailDelivery::query()->firstOrFail();
        $firstBatch = app(MailQuotaService::class)->reserveNextBatch();
        app(MailQuotaService::class)->release($firstBatch, 'First known failure');

        $this->createDeliveries(1, null, 'other-customer');
        $secondBatch = app(MailQuotaService::class)->reserveNextBatch();
        app(MailQuotaService::class)->release($secondBatch, 'Second known failure');

        $result = app(DeliveryActionService::class)->retryFilteredFailures([
            ...$this->emptyFilters(),
            'automation_id' => (string) $firstDelivery->automation_id,
            'email_template_id' => (string) $firstDelivery->email_template_id,
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
        ]);

        $this->assertSame(2, $result['queued']);
        $this->assertSame(1, EmailDelivery::query()->where('status', EmailDeliveryStatus::FAILED)->count());
    }

    public function test_bulk_retry_is_idempotent_and_does_not_touch_non_failed_deliveries(): void
    {
        Queue::fake();
        $this->createDeliveries(3);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        app(MailQuotaService::class)->release($batch, 'Known SMTP failure');
        EmailDelivery::query()->firstOrFail()->update(['status' => EmailDeliveryStatus::CANCELLED]);

        $first = app(DeliveryActionService::class)->retryFilteredFailures($this->emptyFilters());
        $second = app(DeliveryActionService::class)->retryFilteredFailures($this->emptyFilters());

        $this->assertSame(2, $first['queued']);
        $this->assertSame(['queued' => 0, 'skipped' => 0, 'deferred' => 0], $second);
        $this->assertSame(1, EmailDelivery::query()->where('status', EmailDeliveryStatus::CANCELLED)->count());
        Queue::assertPushed(SendEmailBatchJob::class, 1);
    }

    public function test_bulk_retry_defers_deliveries_that_do_not_fit_daily_quota(): void
    {
        Queue::fake();
        $this->createDeliveries(75);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        app(MailQuotaService::class)->release($batch, 'Known SMTP failure');
        config(['mail.automation.daily_limit' => 50]);

        $result = app(DeliveryActionService::class)->retryFilteredFailures($this->emptyFilters());

        $this->assertSame(['queued' => 50, 'skipped' => 0, 'deferred' => 25], $result);
        $this->assertSame(25, EmailDelivery::query()->where('status', EmailDeliveryStatus::PENDING)->count());
    }

    public function test_bulk_retry_skips_a_failed_delivery_attached_to_an_ambiguous_batch(): void
    {
        Queue::fake();
        $this->createDeliveries(2);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        app(MailQuotaService::class)->release($batch, 'Known SMTP failure');
        $batch->update(['status' => EmailDeliveryBatchStatus::SENDING]);

        $result = app(DeliveryActionService::class)->retryFilteredFailures($this->emptyFilters());

        $this->assertSame(['queued' => 0, 'skipped' => 2, 'deferred' => 0], $result);
        Queue::assertNothingPushed();
    }

    public function test_deliveries_index_shows_filtered_failure_count_and_confirmation(): void
    {
        Queue::fake();
        $this->createDeliveries(2);
        $batch = app(MailQuotaService::class)->reserveNextBatch();
        app(MailQuotaService::class)->release($batch, 'Known SMTP failure');

        Livewire::test(DeliveriesIndex::class)
            ->assertSee('Retry Failed (2)')
            ->assertSee('Retry all 2 failed deliveries matching the current filters?')
            ->set('search', 'missing@example.com')
            ->assertSee('Retry Failed (0)');
    }

    private function assertBatchDelivery(int $recipients, array $expectedSizes): void
    {
        Mail::fake();
        $this->createDeliveries($recipients);

        app(EmailBatchDispatcher::class)->dispatchDue(20);

        Mail::assertSent(AutomationMail::class, count($expectedSizes));
        $actualSizes = EmailDeliveryBatch::query()->orderBy('id')->pluck('recipient_count')->all();
        $this->assertSame($expectedSizes, $actualSizes);
        $this->assertSame($recipients, EmailDelivery::query()->where('status', EmailDeliveryStatus::SENT)->count());

        $bccSizes = [];
        Mail::assertSent(AutomationMail::class, function (AutomationMail $mail) use (&$bccSizes): bool {
            $bccSizes[] = count($mail->bcc);

            return count($mail->to) === 1
                && $mail->hasTo((string) config('mail.from.address'));
        });
        $this->assertSame($expectedSizes, $bccSizes);
    }

    private function createDeliveries(
        int $count,
        mixed $scheduledFor = null,
        string $emailPrefix = 'customer'
    ): void {
        $user = User::factory()->create();
        $template = EmailTemplate::query()->create([
            'name' => 'Static announcement',
            'subject' => 'Common subject',
            'body_html' => '<p>Common body</p>',
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

        for ($index = 1; $index <= $count; $index++) {
            $customer = Customer::query()->create([
                'name' => "Customer {$index}",
                'email' => "{$emailPrefix}{$index}@example.com",
                'source' => UserSource::MANUAL,
                'signup_at' => now(),
            ]);
            EmailDelivery::query()->create([
                'automation_id' => $automation->id,
                'customer_id' => $customer->id,
                'email_template_id' => $template->id,
                'status' => EmailDeliveryStatus::PENDING,
                'scheduled_for' => $scheduledFor ?? now(),
            ]);
        }
    }

    private function emptyFilters(): array
    {
        return [
            'search' => '',
            'status' => null,
            'automation_id' => null,
            'email_template_id' => null,
            'date_from' => null,
            'date_to' => null,
        ];
    }
}
