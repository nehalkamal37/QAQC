<?php

namespace App\Services;

use App\Models\QAItem;
use App\Models\ActivityLog;
use Carbon\Carbon;

class WeeklyReportService
{
    public function generate()
    {
        $from = Carbon::now()->subDays(7);

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
