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
                'schedule_type' => $data['schedule_type'],
                'email_template_id' => $data['email_template_id'],
                'created_by' => $data['created_by'],
                'is_active' => $data['is_active'],
            ]);

            $automation->schedule()->create([
                'delay_days' => $data['delay_days'],
                'weekday' => $data['weekday'],
                'month_day' => $data['month_day'],
                'send_time' => $data['send_time'],
            ]);

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
                'schedule_type' => $data['schedule_type'],
                'email_template_id' => $data['email_template_id'],
                'is_active' => $data['is_active'],
            ]);

            $automation->schedule()->update([
                'delay_days' => $data['delay_days'],
                'weekday' => $data['weekday'],
                'month_day' => $data['month_day'],
                'send_time' => $data['send_time'],
            ]);

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
}