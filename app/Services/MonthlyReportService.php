<?php
namespace App\Services;

use App\Models\QAItem;
use App\Models\ActivityLog;
use Carbon\Carbon;

class MonthlyReportService
{
    public function generate()
    {
      //  $from = Carbon::now()->subMonth();
        $from = Carbon::now()->subDays(10);  //Everything in the report is for the last 7 days

        return [
            'created_items' => QAItem::where('created_at', '>=', $from)->count(),
            'closed_items' => QAItem::where('status', 'closed')
                ->where('updated_at', '>=', $from)
                ->count(),

            'by_status' => QAItem::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),

            'recent_activities' => ActivityLog::where('created_at', '>=', $from)
                ->latest()
                ->take(10)
                ->get(),
        ];

        
    }
}
