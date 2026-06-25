<?php

namespace App\Livewire\Automations;

use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use App\Models\Automation;
use App\Models\EmailTemplate;
use App\Services\Automation\AutomationService;
use Livewire\Component;

class Edit extends Component
{
    public Automation $automation;

    public string $name = '';

    public ?string $description = null;

    public string $target_user_type = '';

    public string $schedule_type = '';

    public int|string|null $email_template_id = null;

    public bool $is_active = true;

    public ?int $delay_days = null;

    public ?int $weekday = null;

    public ?int $month_day = null;

    public ?string $send_time = '09:00';

    public function mount(
        Automation $automation
    ): void {

        $this->automation = $automation->load('schedule');

        $this->name = $automation->name;

        $this->description = $automation->description;

        $this->target_user_type = $automation
            ->target_user_type
            ->value;

        $this->schedule_type = $automation
            ->schedule_type
            ->value;

        $this->email_template_id = $automation
            ->email_template_id;

        $this->is_active = $automation
            ->is_active;

        $this->delay_days = $automation
            ->schedule?->delay_days;

        $this->weekday = $automation
            ->schedule?->weekday;

        $this->month_day = $automation
            ->schedule?->month_day;

        $this->send_time = $automation
            ->schedule?->send_time?->format('H:i')
            ?? '09:00';
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email_template_id' => ['required', 'exists:email_templates,id'],
            'target_user_type' => ['required'],
            'schedule_type' => ['required'],
        ];
    }

    public function save(
        AutomationService $service
    ): void {

        $this->validate();

        $service->update(
            $this->automation,
            [
                'name' => $this->name,
                'description' => $this->description,
                'target_user_type' => $this->target_user_type,
                'schedule_type' => $this->schedule_type,
                'email_template_id' => $this->email_template_id,
                'is_active' => $this->is_active,
                'delay_days' => $this->delay_days,
                'weekday' => $this->weekday,
                'month_day' => $this->month_day,
                'send_time' => $this->send_time,
            ]
        );

        session()->flash(
            'success',
            'Automation updated successfully.'
        );

        $this->redirectRoute('automations.index');
    }

    public function render()
    {
        return view('livewire.automations.edit', [
            'templates' => EmailTemplate::query()
                ->orderBy('name')
                ->get(),

            'targetTypes' => TargetUserType::cases(),

            'scheduleTypes' => ScheduleType::cases(),
        ]);
    }
}