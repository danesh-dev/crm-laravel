<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'started_at', 'expires_at', 'cancelled_at'])]
class Subscription extends Model
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query
            ->whereNull('cancelled_at')
            ->where('started_at', '<=', now())
            ->where('expires_at', '>', now());
    }

    public function scopeExpired(Builder $query): void
    {
        $query
            ->whereNull('cancelled_at')
            ->where('expires_at', '<=', now());
    }

    public function scopeCancelled(Builder $query): void
    {
        $query->whereNotNull('cancelled_at');
    }

    protected function isActive(): Attribute
    {
        return Attribute::get(fn (): bool => $this->cancelled_at === null
            && $this->started_at !== null
            && $this->expires_at !== null
            && $this->started_at->lte(now())
            && $this->expires_at->gt(now()));
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
