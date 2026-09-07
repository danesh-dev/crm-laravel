<?php

namespace App\Enums;

enum EmailDeliveryBatchStatus: string
{
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case SENDING = 'sending';
    case SENT = 'sent';
    case FAILED = 'failed';
}
