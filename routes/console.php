<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Schedule::command('test:cron')->everySecond();

Schedule::command('test:cron')->everyFiveMinutes();

// $schedule->command('mycommand:run')
//     ->cron('0 0 * * 1,3,5'); // Runs at midnight (00:00) on Mondays, Wednesdays, and Fridays

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
