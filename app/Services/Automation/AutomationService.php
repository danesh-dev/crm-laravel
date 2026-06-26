<?php

namespace App\Services\Automation;

use App\Models\Automation;
use Illuminate\Support\Facades\DB;

class AutomationService
{
    public function create(array $data): Automation
    {
        return DB::transaction(function () use ($data) {

            $automation = Automation::query()->create([
                'name' => $data['name'],
                'description' => $data['description'],
                'target_user_type' => $data['target_user_type'],
                'target_offset_days' => $data['target_offset_days'],
                'schedule_type' => $data['schedule_type'],
                'email_template_id' => $data['email_template_id'],
                'created_by' => $data['created_by'],
                'is_active' => $data['is_active'],
                'repeat_count' => $data['repeat_count'],
            ]);

            $automation->schedule()->create(
                $this->scheduleData($data)
            );

            return $automation;
        });
    }

    public function update(
        Automation $automation,
        array $data
    ): Automation {

        return DB::transaction(function () use ($automation, $data) {

            $automation->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'target_user_type' => $data['target_user_type'],
                'target_offset_days' => $data['target_offset_days'],
                'schedule_type' => $data['schedule_type'],
                'email_template_id' => $data['email_template_id'],
                'is_active' => $data['is_active'],
                'repeat_count' => $data['repeat_count'],
            ]);

            $automation->schedule()->updateOrCreate(
                ['automation_id' => $automation->id],
                $this->scheduleData($data)
            );

            return $automation->fresh();
        });
    }

    public function delete(
        Automation $automation
    ): void {

        DB::transaction(function () use ($automation) {

            $automation->schedule()?->delete();

            $automation->delete();
        });
    }

    private function scheduleData(array $data): array
    {
        return [
            'run_date' => $data['run_date'],
            'delay_days' => $data['delay_days'],
            'weekday' => $data['weekday'],
            'month_day' => $data['month_day'],
            'send_time' => $data['send_time'],
        ];
    }
}
