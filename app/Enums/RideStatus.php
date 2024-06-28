<?php

namespace App\Enums;

enum RideStatus: string
{
    case Waiting = 'waiting';
    case Accepted = 'accepted';
    case InProgress = 'in-progress';
    case Finished = 'finished';
}
