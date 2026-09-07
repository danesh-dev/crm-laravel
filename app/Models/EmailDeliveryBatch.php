<?php

namespace App\Models;

use App\Enums\EmailDeliveryBatchStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'automation_id',
    'email_template_id',
    'quota_date',
    'scheduled_for',
    'available_at',
    'dispatched_at',
    'processing_at',
    'sending_at',
    'sent_at',
    'failed_at',
    'recipient_count',
    'status',
    'provider_message_id',
    'error_message',
])]
class EmailDeliveryBatch extends Model
{
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(EmailDelivery::class);
    }

    protected function casts(): array
    {
        return [
            'status' => EmailDeliveryBatchStatus::class,
            'quota_date' => 'date:Y-m-d',
            'scheduled_for' => 'datetime',
            'available_at' => 'datetime',
            'dispatched_at' => 'datetime',
            'processing_at' => 'datetime',
            'sending_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'recipient_count' => 'integer',
        ];
    }
}
