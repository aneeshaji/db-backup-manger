<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Schedule::command('test:cron')->everyFiveMinutes();

Schedule::command('database-backup:cron')->everyFiveMinutes();

// $schedule->command('database-backup:cron')
//     ->cron('0 0 * * 1,3,5'); // Runs at midnight (00:00) on Mondays, Wednesdays, and Fridays

