<?php

namespace App\Models;

use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'description', 'target_user_type', 'schedule_type', 'is_active', 'email_template_id', 'created_by'])]
class Automation extends Model
{
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(AutomationSchedule::class);
    }

    public function emailDeliveries(): HasMany
    {
        return $this->hasMany(EmailDelivery::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'target_user_type' => TargetUserType::class,
            'schedule_type' => ScheduleType::class,
            'is_active' => 'boolean',
        ];
    }
}
