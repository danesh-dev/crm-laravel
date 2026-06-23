<?php

namespace App\Services\Automation;

use App\Enums\ScheduleType;
use App\Models\Automation;
use App\Services\Email\EmailScheduler;

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

    protected function processAutomation(
        Automation $automation
    ): void {

        match ($automation->schedule_type) {

            ScheduleType::AFTER_SIGNUP => $this->processAfterSignup($automation),

            ScheduleType::DAILY => $this->processDaily($automation),

            ScheduleType::WEEKLY => $this->processWeekly($automation),

            ScheduleType::MONTHLY => $this->processMonthly($automation),
        };
    }

    protected function processAfterSignup(
        Automation $automation
    ): void {

        $days = $automation
            ->schedule
            ->delay_days;

        $customers = $this->matcher
            ->query($automation)
            ->whereDate(
                'signup_at',
                now()->subDays($days)->toDateString()
            )
            ->get();

        foreach ($customers as $customer) {

            $this->scheduler->schedule(
                automation: $automation,
                customer: $customer,
                scheduledFor: now()
            );
        }
    }

    protected function processDaily(
        Automation $automation
    ): void {

        $customers = $this->matcher
            ->query($automation)
            ->get();

        foreach ($customers as $customer) {

            $this->scheduler->schedule(
                automation: $automation,
                customer: $customer,
                scheduledFor: now()
            );
        }
    }

    protected function processWeekly(
        Automation $automation
    ): void {

        if (
            now()->dayOfWeek !==
            $automation->schedule->weekday
        ) {
            return;
        }

        $customers = $this->matcher
            ->query($automation)
            ->get();

        foreach ($customers as $customer) {

            $this->scheduler->schedule(
                automation: $automation,
                customer: $customer,
                scheduledFor: now()
            );
        }
    }

    protected function processMonthly(
        Automation $automation
    ): void {

        if (
            now()->day !==
            $automation->schedule->month_day
        ) {
            return;
        }

        $customers = $this->matcher
            ->query($automation)
            ->get();

        foreach ($customers as $customer) {

            $this->scheduler->schedule(
                automation: $automation,
                customer: $customer,
                scheduledFor: now()
            );
        }
    }
}
