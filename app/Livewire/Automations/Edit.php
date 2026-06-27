<?php

namespace App\Livewire\Automations;

use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use App\Models\Automation;
use App\Models\EmailTemplate;
use App\Models\Tag;
use App\Services\Automation\AutomationService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Automation $automation;

    public string $name = '';

    public ?string $description = null;

    public string $target_user_type = '';

    public ?int $target_offset_days = null;

    public string $schedule_type = '';

    public int|string|null $email_template_id = null;

    public bool $is_active = true;

    public ?string $run_date = null;

    public ?int $delay_days = null;

    public ?int $weekday = null;

    public ?int $month_day = null;

    public ?string $send_time = '09:00';

    public string $repeat_mode = 'unlimited';

    public ?int $repeat_count = null;

    /** @var array<int, int|string> */
    public array $tagIds = [];

    public function mount(
        Automation $automation
    ): void {

        $this->automation = $automation->load(['schedule', 'tags:id']);

        $this->name = $automation->name;

        $this->description = $automation->description;

        $this->target_user_type = $automation
            ->target_user_type
            ->value;

        $this->target_offset_days = $automation
            ->target_offset_days;

        $this->schedule_type = $automation
            ->schedule_type
            ->value;

        $this->email_template_id = $automation
            ->email_template_id;

        $this->is_active = $automation
            ->is_active;

        $this->repeat_count = $automation->repeat_count;
        $this->repeat_mode = $automation->repeat_count === null
            ? 'unlimited'
            : 'limited';

        $this->run_date = $automation
            ->schedule?->run_date?->format('Y-m-d');

        $this->delay_days = $automation
            ->schedule?->delay_days;

        $this->weekday = $automation
            ->schedule?->weekday;

        $this->month_day = $automation
            ->schedule?->month_day;

        $this->send_time = $automation
            ->schedule?->send_time?->format('H:i')
            ?? '09:00';

        $this->tagIds = $this->automation->tags
            ->pluck('id')
            ->all();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'email_template_id' => ['required', 'exists:email_templates,id'],
            'target_user_type' => ['required', Rule::enum(TargetUserType::class)],
            'target_offset_days' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'schedule_type' => ['required', Rule::enum(ScheduleType::class)],
            'is_active' => ['boolean'],
            'repeat_mode' => ['required', Rule::in(['unlimited', 'limited'])],
            'repeat_count' => ['nullable', 'required_if:repeat_mode,limited', 'integer', 'min:1', 'max:32767'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', Rule::exists('tags', 'id')->whereNull('deleted_at')],
            'run_date' => ['nullable', 'required_if:schedule_type,'.ScheduleType::ONCE->value, 'date'],
            'delay_days' => ['nullable', 'required_if:schedule_type,'.ScheduleType::AFTER_SIGNUP->value, 'integer', 'min:0'],
            'weekday' => ['nullable', 'required_if:schedule_type,'.ScheduleType::WEEKLY->value, 'integer', 'between:0,6'],
            'month_day' => ['nullable', 'required_if:schedule_type,'.ScheduleType::MONTHLY->value, 'integer', 'between:1,31'],
            'send_time' => ['required', 'date_format:H:i'],
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
                'target_offset_days' => $this->normalizedTargetOffsetDays(),
                'schedule_type' => $this->schedule_type,
                'email_template_id' => $this->email_template_id,
                'is_active' => $this->is_active,
                'repeat_count' => $this->normalizedRepeatCount(),
                'tag_ids' => $this->tagIds,
                'run_date' => $this->schedule_type === ScheduleType::ONCE->value ? $this->run_date : null,
                'delay_days' => $this->schedule_type === ScheduleType::AFTER_SIGNUP->value ? $this->delay_days : null,
                'weekday' => $this->schedule_type === ScheduleType::WEEKLY->value ? $this->weekday : null,
                'month_day' => $this->schedule_type === ScheduleType::MONTHLY->value ? $this->month_day : null,
                'send_time' => $this->send_time,
            ]
        );

        session()->flash(
            'success',
            'Automation updated successfully.'
        );

        $this->redirectRoute('automations.index');
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

        return view('livewire.automations.edit', [
            'templates' => $templates,

            'selectedTemplate' => $templates->firstWhere('id', (int) $this->email_template_id),

            'targetTypes' => TargetUserType::cases(),

            'scheduleTypes' => ScheduleType::cases(),

            'tags' => Tag::query()
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
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
