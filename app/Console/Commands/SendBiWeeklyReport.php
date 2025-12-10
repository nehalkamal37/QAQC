<?php
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AnalyticsController; // اللي فيها phaseGateAnalytics()

class SendBiWeeklyReport extends Command
{
    protected $signature = 'report:biweekly';
    protected $description = 'Send bi-weekly QA/QC analytics email';

    public function handle()
    {
        $controller = new AnalyticsController();
        $request = new \Illuminate\Http\Request();
        $analytics = $controller->phaseGateAnalytics($request)->getData(true);

        Mail::to(['nehalk751@gmail.com','nehalk751@gmail.com'])
            ->send(new \App\Mail\BiWeeklyReportMail($analytics));

        $this->info('Bi-weekly report sent successfully.');
    }
}


