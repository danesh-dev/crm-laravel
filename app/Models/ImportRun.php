<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type',
    'status',
    'original_filename',
    'file_path',
    'total_records',
    'processed_records',
    'customers_created',
    'customers_updated',
    'subscriptions_created',
    'duplicate_subscriptions_skipped',
    'invalid_rows',
    'errors',
    'started_at',
    'finished_at',
    'processing_time_ms',
    'created_by',
])]
class ImportRun extends Model
{
    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
