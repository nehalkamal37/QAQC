<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Services\WeeklyReportService;
use App\Mail\WeeklyReportMail;
use App\Models\ReportSchedule;
use App\Models\User;

class SendWeeklyReport extends Command
{
    protected $signature = 'report:weekly';
    protected $description = 'Send weekly QA/QC report to all users';

    public function handle()
    {
        $schedule = ReportSchedule::where('type', 'weekly')
            ->where('enabled', true)
            ->first();

        if (! $schedule) {
            $this->warn('No enabled weekly schedule found.');
            return;
        }

        $report = app(WeeklyReportService::class)->generate();

        User::whereNotNull('email')
            ->chunk(50, function ($users) use ($report) {
                foreach ($users as $user) {
                    Mail::to($user->email)->send(
                        new WeeklyReportMail($report)
                    );
                }
            });

        $this->info('Weekly report sent to all users.');
    }
}
