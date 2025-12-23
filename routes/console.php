<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\ReportSchedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
Schedule::command('report:weekly')->everyMinute();
Schedule::command('report:monthly')->everyMinute();
*/


Schedule::command('report:weekly')->hourly();
Schedule::command('report:monthly')->hourly();







  // Schedule::command('report:weekly')->cron('0 10 * * 5');
   // ->weeklyOn(5, '10:00'); // كل جمعة الساعة 10

   // Schedule::command('report:weekly')->everyMinute();

