<?php

namespace App\Enums;

enum ScheduleType: string
{
    case ONCE = 'once';
    case AFTER_SIGNUP = 'after_signup';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
}
