<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $report;

    public function __construct(array $report)
    {
        $this->report = $report;
    }

    public function build()
    {
        return $this->subject('Weekly QA/QC Report')
            ->view('emails.weekly-report');
    }
}

