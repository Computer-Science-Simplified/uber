<?php

namespace App\Enums;

enum RedisKey: string
{
    case DriverPoolAvailable = 'drivers:available';

    case DriverPoolUnavailable = 'drivers:unavailable';

    case DriverPoolOnHold = 'drivers:on-hold';

    case DriverCurrentLocations = 'drivers:current-locations';
}
