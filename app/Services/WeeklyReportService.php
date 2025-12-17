<?php

namespace App\Services;

use App\Models\QAItem;
use App\Models\ActivityLog;
use Carbon\Carbon;

class WeeklyReportService
{
    public function generate()
    {
        $from = Carbon::now()->subDays(7);  //Everything in the report is for the last 7 days

        return [
            'created_items' => QAItem::where('created_at', '>=', $from)->count(), //How many QA items were created in the last 7 days?

            'closed_items' => QAItem::where('status', 'closed')
                ->where('updated_at', '>=', $from)   //Step 3: Count closed QA items this week
                ->count(),

            'by_status' => QAItem::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),    //Step 4: Group QA items by status,How many QA items are in each status right now?

            'recent_activities' => ActivityLog::where('created_at', '>=', $from)  //Step 5: Get recent activities
                ->latest()
                ->take(10)
                ->get(),
        ];

    }
    
}
