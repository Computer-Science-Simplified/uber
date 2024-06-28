<?php

use App\Jobs\CleanUpWaitingRides;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new CleanUpWaitingRides())->everyMinute();
