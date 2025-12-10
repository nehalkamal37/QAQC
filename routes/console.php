<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\BiWeeklyReportMail;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::command('report:biweekly')
    ->mondays()
    ->when(fn () => Carbon::now()->weekOfYear % 2 === 0)
    ->at('08:00')
    ->runInBackground();


