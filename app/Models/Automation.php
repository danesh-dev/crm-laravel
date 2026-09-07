<?php

namespace App\Models;

use App\Enums\ScheduleType;
use App\Enums\TargetUserType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'description', 'target_user_type', 'target_offset_days', 'schedule_type', 'is_active', 'repeat_count', 'email_template_id', 'created_by'])]
class Automation extends Model
{
    use SoftDeletes;

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

    public function emailDeliveryBatches(): HasMany
    {
        return $this->hasMany(EmailDeliveryBatch::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'target_user_type' => TargetUserType::class,
            'target_offset_days' => 'integer',
            'schedule_type' => ScheduleType::class,
            'is_active' => 'boolean',
            'repeat_count' => 'integer',
        ];
    }
}
