<?php

namespace App\Enums;

enum TargetUserType: string
{
    case ALL = 'all';
    case NEVER_SUBSCRIBED = 'never_subscribed';
    case ACTIVE_SUBSCRIBER = 'active_subscriber';
    case EXPIRED_SUBSCRIBER = 'expired_subscriber';
    case CANCELLED_SUBSCRIBER = 'cancelled_subscriber';
}
