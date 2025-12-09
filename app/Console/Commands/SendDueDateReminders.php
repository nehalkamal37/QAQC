<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\QaItem;
use App\Services\NotificationService;
use Carbon\Carbon;
class SendDueDateReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send-due-date';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for QA items that are due soon';
    protected $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    // Create a command for due date reminders
// app/Console/Commands/SendDueDateReminders.php
// app/Console/Commands/CheckDueDates.php
// In SendDueDateReminders command - fix the calculation
public function handle()
{
    $dueSoonItems = \App\Models\QaItem::where('due_date', '<=', now()->addDays(3))
        ->whereNotIn('status', ['closed', 'verified'])
        ->get();

    foreach ($dueSoonItems as $item) {
        // Use floor() and compare startOfDay to ignore time portions
        $daysUntilDue = now()->startOfDay()->diffInDays($item->due_date->startOfDay(), false);
        
        if ($item->assigned_to) {
            \App\Services\NotificationService::notifyDueDateReminder(
                $item->assigned_to,
                $item->id,
                $daysUntilDue
            );
        }
    }
    
    $this->info("Sent due date reminders for " . $dueSoonItems->count() . " items");
}
}