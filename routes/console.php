<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('responses:mark-abandoned')->hourly();
Schedule::command('reports:send-scheduled')->daily();
Schedule::command('survyra:weekly-digest')->weeklyOn(1, '08:00');

Schedule::command('backup:run')->dailyAt('01:00')->onOneServer();
Schedule::command('backup:clean')->dailyAt('01:30')->onOneServer();
Schedule::command('backup:monitor')->dailyAt('02:00')->onOneServer();
