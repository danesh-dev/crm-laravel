<?php

namespace App\Enums;

enum UserSource:string
{
    case MANUAL = 'manual';
    case FIREBASE = 'firebase';
}