<?php

namespace App\Services;

use App\Models\QAItem;
use App\Models\ActivityLog;
use App\Models\ProjectQAItemStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WeeklyReportExportService
{
    public function build(): array
    {
        $from = Carbon::now()->subDays(7);
        $prev = Carbon::now()->subDays(14);
        $now  = Carbon::now();

        /* ============================================================
         |  BASIC THROUGHPUT
         * ============================================================ */

        $createdThisWeek = QAItem::where('created_at', '>=', $from)->count();
        $createdLastWeek = QAItem::whereBetween('created_at', [$prev, $from])->count();

       

            $closedThisWeek = ProjectQAItemStatus::where('confirmed', true)
    ->where('updated_at', '>=', $from)
    ->count();


        /* ============================================================
         |  STATUS DISTRIBUTION (PROJECT CONTEXT)
         * ============================================================ */

        $statusCounts = QAItem::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalStatusItems = $statusCounts->sum();

        $statusDistribution = $statusCounts->map(function ($count) use ($totalStatusItems) {
            return [
                'count' => $count,
                'percentage' => $totalStatusItems > 0
                    ? round(($count / $totalStatusItems) * 100, 2)
                    : 0,
            ];
        });

        /* ============================================================
         |  A / I / C ANALYSIS
         * ============================================================ */

        $aic = [
            'applicable_total'   => ProjectQAItemStatus::where('applicable', true)->count(),
            'incorporated_total' => ProjectQAItemStatus::where('incorporated', true)->count(),
            'confirmed_total'    => ProjectQAItemStatus::where('confirmed', true)->count(),

            'became_applicable_this_week' => ProjectQAItemStatus::where('applicable', true)
                ->where('updated_at', '>=', $from)->count(),

            'became_confirmed_this_week' => ProjectQAItemStatus::where('confirmed', true)
                ->where('updated_at', '>=', $from)->count(),
        ];

        /* ============================================================
         |  OVERDUE & RISK
         * ============================================================ */

        $overdue = [
            'total_overdue' => ProjectQAItemStatus::where('confirmed', false)
                ->where('due_date', '<', $now)
                ->count(),

            'critical_overdue' => ProjectQAItemStatus::where('confirmed', false)
                ->where('due_date', '<', $now)
                ->whereHas('qaItem', fn ($q) => $q->where('severity', 'critical'))
                ->count(),

            'avg_days_overdue' => ProjectQAItemStatus::where('confirmed', false)
                ->where('due_date', '<', $now)
                ->selectRaw('AVG(DATEDIFF(NOW(), due_date)) as avg_days')
                ->value('avg_days'),
        ];

        /* ============================================================
         |  SEVERITY ANALYSIS
         * ============================================================ */

        $severityDistribution = ProjectQAItemStatus::join(
                'qa_items',
                'qa_items.id',
                '=',
                'project_qa_item_statuses.qa_item_id'
            )
            ->selectRaw('qa_items.severity, COUNT(*) as total')
            ->groupBy('qa_items.severity')
            ->pluck('total', 'qa_items.severity');

        $severityByStatus = ProjectQAItemStatus::join(
        'qa_items',
        'qa_items.id',
        '=',
        'project_qa_item_statuses.qa_item_id'
    )
    ->selectRaw("
        qa_items.severity,
        CASE
            WHEN project_qa_item_statuses.confirmed = 1 THEN 'closed'
            WHEN project_qa_item_statuses.incorporated = 1 THEN 'resolved'
            WHEN project_qa_item_statuses.applicable = 1 THEN 'open'
            ELSE 'not_applicable'
        END as derived_status,
        COUNT(*) as total
    ")
    ->groupBy('qa_items.severity', 'derived_status')
    ->get()
    ->groupBy('severity');

        /* ============================================================
         |  ASSIGNEE PERFORMANCE
         * ============================================================ */

        $assigneeLoad = ProjectQAItemStatus::join(
                'users',
                'users.id',
                '=',
                'project_qa_item_statuses.assigned_to'
            )
            ->selectRaw('users.name, COUNT(*) as assigned')
            ->groupBy('users.name')
            ->pluck('assigned', 'users.name');

        $assigneeClosed = ProjectQAItemStatus::join(
                'users',
                'users.id',
                '=',
                'project_qa_item_statuses.assigned_to'
            )
            ->where('confirmed', true)
            ->selectRaw('users.name, COUNT(*) as closed')
            ->groupBy('users.name')
            ->pluck('closed', 'users.name');

        /* ============================================================
         |  AGING ANALYSIS
         * ============================================================ */

        $aging = [
            'avg_open_days' => ProjectQAItemStatus::where('confirmed', false)
                ->selectRaw('AVG(DATEDIFF(NOW(), created_at)) as avg_days')
                ->value('avg_days'),

            'older_than_14_days' => ProjectQAItemStatus::where('confirmed', false)
                ->where('created_at', '<', now()->subDays(14))
                ->count(),

            'older_than_30_days' => ProjectQAItemStatus::where('confirmed', false)
                ->where('created_at', '<', now()->subDays(30))
                ->count(),
        ];

        /* ============================================================
         |  WORKFLOW HEALTH (ACTIVITY BASED)
         * ============================================================ */

        $activitySummary = ActivityLog::where('created_at', '>=', $from)
            ->selectRaw('action_type, COUNT(*) as total')
            ->groupBy('action_type')
            ->pluck('total', 'action_type');

        $statusTransitions = ActivityLog::where('created_at', '>=', $from)
            ->where('action_type', 'status_change')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(new_value, '$.status')) as status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        /* ============================================================
         |  FINAL DATASET
         * ============================================================ */

        return [
            'meta' => [
                'from'         => $from->toDateString(),
                'to'           => $now->toDateString(),
                'generated_at' => $now->toDateTimeString(),
            ],

            'throughput' => [
                'created_this_week' => $createdThisWeek,
                'created_last_week' => $createdLastWeek,
                'closed_this_week'  => $closedThisWeek,
                'net_change'        => $createdThisWeek - $closedThisWeek,
            ],

            'status_distribution' => $statusDistribution,

            'aic' => $aic,

            'overdue' => $overdue,

            'severity' => [
                'distribution' => $severityDistribution,
                'by_status'    => $severityByStatus,
            ],

            'assignees' => [
                'load'   => $assigneeLoad,
                'closed' => $assigneeClosed,
            ],

            'aging' => $aging,

            'activity' => [
                'summary'     => $activitySummary,
                'transitions' => $statusTransitions,
            ],
        ];
    }
}
