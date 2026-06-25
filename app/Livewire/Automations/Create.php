<?php

namespace App\Livewire\Automations;

use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use App\Models\EmailTemplate;
use App\Services\Automation\AutomationService;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public ?string $description = null;

    public string $target_user_type;

    public string $schedule_type;

    public int $email_template_id;

    public bool $is_active = true;

    public ?int $delay_days = null;

    public ?int $weekday = null;

    public ?int $month_day = null;

    public string $send_time = '09:00';

    public function mount(): void
    {
        $this->target_user_type =
            TargetUserType::ALL->value;

        $this->schedule_type =
            ScheduleType::AFTER_SIGNUP->value;
    }

    public function save(
        AutomationService $service
    ): void {

        $this->validate([
            'name' => ['required'],
            'email_template_id' => ['required'],
        ]);

        $service->create([
            'name' => $this->name,
            'description' => $this->description,
            'target_user_type' => $this->target_user_type,
            'schedule_type' => $this->schedule_type,
            'email_template_id' => $this->email_template_id,
            'created_by' => auth()->id(),
            'is_active' => $this->is_active,
            'delay_days' => $this->delay_days,
            'weekday' => $this->weekday,
            'month_day' => $this->month_day,
            'send_time' => $this->send_time,
        ]);

        $this->redirectRoute(
            'automations.index'
        );
    }

    public function render()
    {
        return view('livewire.automations.create', [
            'templates' => EmailTemplate::all(),
            'scheduleTypes' => ScheduleType::cases(),
            'targetTypes' => TargetUserType::cases(),
        ]);
    }
}