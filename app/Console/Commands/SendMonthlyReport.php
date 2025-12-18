<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\WeeklyReportMail; // you may rename later to MonthlyReportMail
use App\Models\ReportSchedule;
use App\Services\MonthlyReportService;
use App\Models\User;

class SendMonthlyReport extends Command
{
    protected $signature = 'report:monthly';
    protected $description = 'Send monthly QA/QC report to all users';

    public function handle()
    {
        $schedule = ReportSchedule::where('type', 'monthly')
            ->where('enabled', true)
            ->first();

        if (! $schedule) {
            $this->warn('No enabled monthly schedule found.');
            return;
        }

        $report = app(MonthlyReportService::class)->generate();

        User::whereNotNull('email')
            ->chunk(50, function ($users) use ($report) {
                foreach ($users as $user) {
                    Mail::to($user->email)->send(
                        new WeeklyReportMail($report)
                    );
                }
            });

        $this->info('Monthly report sent to all users.');
    }
}
