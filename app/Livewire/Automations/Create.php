<?php

namespace App\Livewire\Automations;

use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use App\Models\EmailTemplate;
use App\Services\Automation\AutomationService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public ?string $description = null;

    public string $target_user_type;

    public ?int $target_offset_days = null;

    public string $schedule_type;

    public int|string|null $email_template_id = null;

    public bool $is_active = true;

    public ?string $run_date = null;

    public ?int $delay_days = null;

    public ?int $weekday = null;

    public ?int $month_day = null;

    public string $send_time = '09:00';

    public string $repeat_mode = 'unlimited';

    public ?int $repeat_count = null;

    public function mount(): void
    {
        $this->target_user_type =
            TargetUserType::ALL->value;

        $this->schedule_type =
            ScheduleType::ONCE->value;
    }

    public function save(
        AutomationService $service
    ): void {

        $this->validate($this->rules());

        $service->create([
            'name' => $this->name,
            'description' => $this->description,
            'target_user_type' => $this->target_user_type,
            'target_offset_days' => $this->normalizedTargetOffsetDays(),
            'schedule_type' => $this->schedule_type,
            'email_template_id' => $this->email_template_id,
            'created_by' => auth()->id(),
            'is_active' => $this->is_active,
            'repeat_count' => $this->normalizedRepeatCount(),
            'run_date' => $this->schedule_type === ScheduleType::ONCE->value ? $this->run_date : null,
            'delay_days' => $this->schedule_type === ScheduleType::AFTER_SIGNUP->value ? $this->delay_days : null,
            'weekday' => $this->schedule_type === ScheduleType::WEEKLY->value ? $this->weekday : null,
            'month_day' => $this->schedule_type === ScheduleType::MONTHLY->value ? $this->month_day : null,
            'send_time' => $this->send_time,
        ]);

        $this->redirectRoute(
            'automations.index'
        );
    }

    public function updatedTargetUserType(string $targetUserType): void
    {
        $this->target_offset_days = $targetUserType === TargetUserType::NEWLY_REGISTERED->value
            ? ($this->target_offset_days ?? 0)
            : null;
    }

    public function render(): View
    {
        $templates = EmailTemplate::query()
            ->select(['id', 'name', 'subject', 'body_html'])
            ->orderBy('name')
            ->get();

        return view('livewire.automations.create', [
            'templates' => $templates,
            'selectedTemplate' => $templates->firstWhere('id', (int) $this->email_template_id),
            'scheduleTypes' => ScheduleType::cases(),
            'targetTypes' => TargetUserType::cases(),
        ]);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_user_type' => ['required', Rule::enum(TargetUserType::class)],
            'target_offset_days' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'schedule_type' => ['required', Rule::enum(ScheduleType::class)],
            'email_template_id' => ['required', 'exists:email_templates,id'],
            'is_active' => ['boolean'],
            'repeat_mode' => ['required', Rule::in(['unlimited', 'limited'])],
            'repeat_count' => ['nullable', 'required_if:repeat_mode,limited', 'integer', 'min:1', 'max:32767'],
            'run_date' => ['nullable', 'required_if:schedule_type,'.ScheduleType::ONCE->value, 'date'],
            'delay_days' => ['nullable', 'required_if:schedule_type,'.ScheduleType::AFTER_SIGNUP->value, 'integer', 'min:0'],
            'weekday' => ['nullable', 'required_if:schedule_type,'.ScheduleType::WEEKLY->value, 'integer', 'between:0,6'],
            'month_day' => ['nullable', 'required_if:schedule_type,'.ScheduleType::MONTHLY->value, 'integer', 'between:1,31'],
            'send_time' => ['required', 'date_format:H:i'],
        ];
    }

    private function normalizedRepeatCount(): ?int
    {
        return $this->repeat_mode === 'limited'
            ? $this->repeat_count
            : null;
    }

    private function normalizedTargetOffsetDays(): ?int
    {
        return $this->target_user_type === TargetUserType::NEWLY_REGISTERED->value
            || ($this->target_user_type === TargetUserType::EXPIRED_SUBSCRIBER->value && $this->target_offset_days !== null)
            ? ($this->target_offset_days ?? 0)
            : null;
    }
}
