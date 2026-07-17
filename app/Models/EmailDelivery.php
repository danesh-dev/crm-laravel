<?php

namespace App\Models;

use App\Enums\EmailDeliveryStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'automation_id',
    'customer_id',
    'email_template_id',
    'status',
    'scheduled_for',
    'processing_at',
    'sent_at',
    'failed_at',
    'provider_message_id',
    'error_message',
])]
class EmailDelivery extends Model
{
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', EmailDeliveryStatus::PENDING->value);
    }

    public function scopeDue(Builder $query): void
    {
        $query->where('scheduled_for', '<=', now(config('app.timezone')));
    }

    protected function casts(): array
    {
        return [
            'status' => EmailDeliveryStatus::class,
            'scheduled_for' => 'datetime',
            'processing_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }
}
