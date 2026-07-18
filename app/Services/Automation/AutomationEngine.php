<?php

namespace App\Services\Automation;

use App\Enums\ScheduleType;
use App\Models\Automation;
use App\Services\Email\EmailScheduler;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Load active automations
 ** Find matching customers
 ** Determine if they should receive an email
 ** Create email_delivery records
 */
class AutomationEngine
{
    public function __construct(
        protected AutomationMatcher $matcher,
        protected EmailScheduler $scheduler,
    ) {}

    public function run(): void
    {
        Automation::query()
            ->active()
            ->with('schedule')
            ->chunkById(100, function ($automations) {

                foreach ($automations as $automation) {

                    $this->processAutomation($automation);

                }
            });
    }

    public function runForAutomation(Automation $automation): int
    {
        $automation->loadMissing('schedule');

        return $this->scheduleCustomers(
            automation: $automation,
            customers: $this->matcher->query($automation),
            scheduledFor: $this->now(),
        );
    }

    protected function processAutomation(
        Automation $automation
    ): void {

        if (! $automation->schedule) {
            return;
        }

        match ($automation->schedule_type) {

            ScheduleType::ONCE => $this->processOnce($automation),

            ScheduleType::AFTER_SIGNUP => $this->processAfterSignup($automation),

            ScheduleType::DAILY => $this->processDaily($automation),

            ScheduleType::WEEKLY => $this->processWeekly($automation),

            ScheduleType::MONTHLY => $this->processMonthly($automation),
        };
    }

    protected function processOnce(
        Automation $automation
    ): void {

        if (! $automation->schedule?->run_date?->isSameDay($this->now())) {
            return;
        }

        $customers = $this->matcher
            ->query($automation);

        $this->scheduleCustomers(
            automation: $automation,
            customers: $customers,
            scheduledFor: $this->scheduledFor($automation),
        );

        $automation->update([
            'is_active' => false,
        ]);
    }

    protected function processAfterSignup(
        Automation $automation
    ): void {

        $days = $automation->schedule->delay_days ?? 0;

        $customers = $this->matcher
            ->query($automation)
            ->whereDate(
                'signup_at',
                $this->now()->subDays($days)->toDateString()
            );

        $this->scheduleCustomers(
            automation: $automation,
            customers: $customers,
            scheduledFor: $this->scheduledFor($automation),
        );
    }

    protected function processDaily(
        Automation $automation
    ): void {

        $customers = $this->matcher
            ->query($automation);

        $this->scheduleCustomers(
            automation: $automation,
            customers: $customers,
            scheduledFor: $this->scheduledFor($automation),
        );
    }

    protected function processWeekly(
        Automation $automation
    ): void {

        if (
            $this->now()->dayOfWeek !==
            $automation->schedule->weekday
        ) {
            return;
        }

        $customers = $this->matcher
            ->query($automation);

        $this->scheduleCustomers(
            automation: $automation,
            customers: $customers,
            scheduledFor: $this->scheduledFor($automation),
        );
    }

    protected function processMonthly(
        Automation $automation
    ): void {

        if (
            $this->now()->day !==
            $automation->schedule->month_day
        ) {
            return;
        }

        $customers = $this->matcher
            ->query($automation);

        $this->scheduleCustomers(
            automation: $automation,
            customers: $customers,
            scheduledFor: $this->scheduledFor($automation),
        );
    }

    private function scheduleCustomers(
        Automation $automation,
        Builder $customers,
        CarbonInterface $scheduledFor
    ): int {
        $queued = 0;

        $customers->chunkById(100, function ($customers) use ($automation, $scheduledFor, &$queued): void {
            foreach ($customers as $customer) {
                $delivery = $this->scheduler->schedule(
                    automation: $automation,
                    customer: $customer,
                    scheduledFor: $scheduledFor
                );

                if ($delivery !== null) {
                    $queued++;
                }
            }
        });

        return $queued;
    }

    private function scheduledFor(Automation $automation): CarbonInterface
    {
        $date = $automation->schedule_type === ScheduleType::ONCE
            ? $automation->schedule->run_date->copy()
            : $this->now();

        $sendTime = $automation->schedule?->send_time;

        if (! $sendTime) {
            return $date;
        }

        return $date->setTime(
            hour: (int) $sendTime->format('H'),
            minute: (int) $sendTime->format('i')
        );
    }

    private function now(): CarbonInterface
    {
        return now(config('app.timezone'));
    }
}
