<?php

namespace App\Enums;

enum RideStatus: string
{
    case Waiting = 'waiting';
    case Approved = 'approved';
    case InProgress = 'in-progress';
    case Finished = 'finished';
}
