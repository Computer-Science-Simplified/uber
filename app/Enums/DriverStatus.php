<?php

namespace App\Enums;

enum DriverStatus: string
{
    case Available = 'available';

    case OnHold = 'on-hold';

    case Unavailable = 'unavailable';
}
