<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'quota_date',
    'sent_messages',
    'reserved_messages',
    'sent_recipients',
    'reserved_recipients',
    'next_send_at',
])]
class MailDailyQuota extends Model
{
    protected function casts(): array
    {
        return [
            'quota_date' => 'date:Y-m-d',
            'sent_messages' => 'integer',
            'reserved_messages' => 'integer',
            'sent_recipients' => 'integer',
            'reserved_recipients' => 'integer',
            'next_send_at' => 'datetime',
        ];
    }
}
