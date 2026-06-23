<?php

namespace App\Enums;

enum EmailDeliveryStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case FAILED = 'failed';
    case BOUNCED = 'bounced';
}
