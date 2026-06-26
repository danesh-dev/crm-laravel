<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['automation_id', 'run_date', 'delay_days', 'weekday', 'month_day', 'send_time'])]
class AutomationSchedule extends Model
{
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    protected function casts(): array
    {
        return [
            'delay_days' => 'integer',
            'weekday' => 'integer',
            'month_day' => 'integer',
            'run_date' => 'date',
            'send_time' => 'datetime',
        ];
    }
}
