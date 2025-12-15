<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\BiWeeklyReportMail;
use App\Models\ReportSchedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('report:biweekly')
    ->mondays()
    ->when(fn () => Carbon::now()->weekOfYear % 2 === 0)
    ->at('08:00')
    ->runInBackground();

Schedule::command('report:weekly')
    ->everyMinute(); // Laravel checks every minute


  // Schedule::command('report:weekly')->cron('0 10 * * 5');
   // ->weeklyOn(5, '10:00'); // كل جمعة الساعة 10

   // Schedule::command('report:weekly')->everyMinute();

/*
Schedule::call(function () {
    $schedule = ReportSchedule::where('type', 'weekly')
    ->where('enabled', true)
    ->first();

    if(! $schedule){
        return;
    }

    Aretisan::call('report:weekly');

})

->cron(fn () => optional(
    ReportSchedule::where('type', 'weekly')->first()
)->cron_expression
);

*/