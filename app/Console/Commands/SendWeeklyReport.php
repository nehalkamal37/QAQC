<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Services\WeeklyReportService;
use App\Mail\WeeklyReportMail;
use App\Models\ReportSchedule;
use Cron\CronExpression;
use App\Models\User;

class SendWeeklyReport extends Command
{

    
    protected $signature = 'report:weekly';
    protected $description = 'Send weekly QA/QC report to admin';
/*
    public function handle(WeeklyReportService $reportService)
    {
        $report = $reportService->generate();

        Mail::to('nehalk751@gmail.com')->send(
            new WeeklyReportMail($report)
        );

        $this->info('Weekly report sent successfully.');
    }
*/


public function handle()
{
    $schedule = ReportSchedule::where('type', 'weekly')
        ->where('enabled', true)
        ->first();

    if (! $schedule) {
        return;
    }

    $cron = new CronExpression($schedule->cron_expression);

    if (! $cron->isDue()) {
        return; // Not time yet
    }
        $report = app(WeeklyReportService::class)->generate();

    // 👇 هنا بس نبعت التقرير
   

        $emails = User::whereNotNull('email')->pluck('email')->toArray();

Mail::to($emails)->send(
    new WeeklyReportMail($report)
);

    $this->info('Weekly report sent.');
}



}
