<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Phase;
use App\Models\Sheet;
use App\Models\QAItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;
use App\Models\QaDailyStat;
use App\Models\ProjectQAItemStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

  public function index()
{
    // Load status table
  $statusCounts = $this->getDerivedStatusCounts();

$data = [
    'projects_count' => Project::count(),
    'phases_count'   => Phase::count(),
    'sheets_count'   => Sheet::count(),
    'qa_items_count' => QaItem::count(),

    // Derived status counts
    'qa_open'        => $statusCounts['open'],
    'qa_pending'     => $statusCounts['in_progress'],
    'qa_needs_info'  => $statusCounts['needs_info'],
    'qa_resolved'    => $statusCounts['resolved'],
    'qa_verified'    => $statusCounts['verified'],
    'qa_closed'      => $statusCounts['closed'],

    // A/I/C
    'qa_applicable'   => ProjectQaItemStatus::where('applicable', 1)->count(),
    'qa_incorporated' => ProjectQaItemStatus::where('incorporated', 1)->count(),
    'qa_confirmed'    => ProjectQaItemStatus::where('confirmed', 1)->count(),

    // Severity
    'qa_critical' => QaItem::where('severity', 'critical')->count(),
    'qa_high'     => QaItem::where('severity', 'high')->count(),
    'qa_medium'   => QaItem::where('severity', 'medium')->count(),
    'qa_low'      => QaItem::where('severity', 'low')->count(),

    'reviewer_load' => User::withCount('qaItemsAssigned')
        ->having('qa_items_assigned_count', '>', 0)
        ->orderBy('qa_items_assigned_count', 'desc')
        ->take(5)
        ->get(),
];


    // Load projects summary
    $projects = Project::withCount(['phases', 'sheets', 'qaItems'])
        ->with(['phases', 'sheets'])
        ->get();

    $data['project_progress'] = $this->getProjectProgress();

    $latestProject = Project::latest('id')->first();

    return view('dashboard.index', [
        'projects' => $projects,
        'data'     => $data,
        'latest_project_id' => $latestProject?->id,
    ]);
}


 private function getDerivedStatusCounts()
{
    $statuses = [
        'open'        => 0,
        'in_progress' => 0,
        'needs_info'  => 0,
        'resolved'    => 0,
        'verified'    => 0,
        'closed'      => 0,
    ];

    // 1️⃣ Load project-based statuses with their QA items
    $rows = ProjectQaItemStatus::with('qaItem')->get();

    foreach ($rows as $row) {

        // Compute derived
        $derived = $row->derived_status 
            ?? ($row->qaItem->status ?? null);

        if ($derived && isset($statuses[$derived])) {
            $statuses[$derived]++;
        }
    }

    // 2️⃣ Count items that DO NOT HAVE project status yet
    $itemsWithoutStatus = QaItem::whereDoesntHave('projectStatus')->get();

    foreach ($itemsWithoutStatus as $item) {
        $raw = $item->status;
        if (isset($statuses[$raw])) {
            $statuses[$raw]++;
        }
    }

    return $statuses;
}





 
/*
   
    public function myWork()
    {
        $assignedItems = QaItem::with(['sheet.phase.project'])
            ->where('assigned_to', Auth::id())
            ->orderByRaw("
                CASE 
                    WHEN status = 'open' THEN 1
                    WHEN status = 'in_progress' THEN 2
                    WHEN status = 'needs_info' THEN 3
                    WHEN status = 'resolved' THEN 4
                    WHEN status = 'verified' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('due_date', 'asc')
            ->get();

        return view('dashboard.my-work', compact('assignedItems'));
    }

*/



    public function myWork1(Request $request)
{
    $query = QaItem::with(['sheet.phase.project'])
        ->where('assigned_to', Auth::id());

    // Apply status filter if provided
    if ($request->has('status') && $request->status) {
        $query->where('status', $request->status);
    }

    $assignedItems = $query->orderByRaw("
            CASE 
                WHEN status = 'open' THEN 1
                WHEN status = 'in_progress' THEN 2
                WHEN status = 'needs_info' THEN 3
                WHEN status = 'resolved' THEN 4
                WHEN status = 'verified' THEN 5
                ELSE 6
            END
        ")
        ->orderBy('due_date', 'asc')
        ->get();

    $overdueCount = $assignedItems->filter(function($item) {
        return $item->isOverdue();
    })->count();

    return view('dashboard.my-work', compact('assignedItems', 'overdueCount'));
}



    public function myWork(Request $request)
    {
        $user = Auth::user();

        // ✅ نجيب الـ assignments الخاصة بالـ user الحالي فقط
        $query = Assignment::with(['project', 'phase'])
            ->active()
            ->where('user_id', $user->id);

        // فلترة اختيارية من الـ form (لو حابة تضيفيها بعدين)
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $assignments = $query
            ->orderBy('assigned_at', 'desc')
            ->get();

        // شوية إحصائيات سريعة للـ header والـ cards
        $stats = [
            'total'           => $assignments->count(),
            'pm'              => $assignments->where('role', 'pm')->count(),
            'senior_reviewer' => $assignments->where('role', 'senior_reviewer')->count(),
            'engineer'        => $assignments->where('role', 'engineer')->count(),
            'eit'             => $assignments->where('role', 'eit')->count(),
            'night_vision'    => $assignments->where('role', 'night_vision')->count(),
        ];

        // projects اللي ظاهر فيها assignments لليوزر
        $projects = Project::whereIn('id', $assignments->pluck('project_id')->unique())->get();

        return view('dashboard.my-work', compact('assignments', 'stats', 'projects'));
    }

    // Get Project Progress  for dashboard
public function getProjectProgress1()
{
    // Load all project structure (phases + sheets)
    $projects = Project::with(['phases.sheets'])->get();

    // Load all QAItems once
    $qaItems = QAItem::select('id', 'sheet_id')->get()->groupBy('sheet_id');

    // Load all statuses once
    $statuses = ProjectQAItemStatus::select(
        'project_id',
        'qa_item_id',
        'applicable',
        'incorporated',
        'confirmed'
    )->get()->groupBy('project_id');

    foreach ($projects as $project) {

        // ---------------------------------
        // 1) PROJECT-LEVEL PROGRESS
        // ---------------------------------

        // Get all QAItems belonging to this project (all sheets across phases)
        $projectSheetIds = $project->phases->flatMap(fn($p) => $p->sheets->pluck('id'));
        $projectQaItemIds = $projectSheetIds->flatMap(fn($sheetId) => $qaItems->get($sheetId, collect())->pluck('id'));

        // Now filter statuses belonging to QA items in this project
        $projectStatuses = $statuses->get($project->id, collect())
            ->whereIn('qa_item_id', $projectQaItemIds);

        $projectApplicable = $projectStatuses->where('applicable', true)->count();
        $projectIncorporated = $projectStatuses->where('incorporated', true)->count();
        $projectConfirmed  = $projectStatuses->where('confirmed', true)->count();

        $project->progress = $projectApplicable > 0
            ? round(($projectConfirmed / $projectApplicable) * 100, 1)
            : 0;


        // ---------------------------------
        // 2) PHASE-LEVEL PROGRESS
        // ---------------------------------
        foreach ($project->phases as $phase) {

            // Sheet IDs in this phase
            $phaseSheetIds = $phase->sheets->pluck('id');

            // QA Items belonging to this phase
            $phaseQaItemIds = $phaseSheetIds->flatMap(
                fn($sheetId) => $qaItems->get($sheetId, collect())->pluck('id')
            );

            // Status rows belonging to this phase
            $phaseStatuses = $projectStatuses->whereIn('qa_item_id', $phaseQaItemIds);

            $phaseApplicable = $phaseStatuses->where('applicable', true)->count();
            $phaseConfirmed  = $phaseStatuses->where('confirmed', true)->count();

            $phase->progress = $phaseApplicable > 0
                ? round(($phaseConfirmed / $phaseApplicable) * 100, 1)
                : 0;


            // ---------------------------------
            // 3) SHEET-LEVEL PROGRESS
            // ---------------------------------
            foreach ($phase->sheets as $sheet) {

                $sheetQaItemIds = $qaItems->get($sheet->id, collect())->pluck('id');

                $sheetStatuses = $projectStatuses->whereIn('qa_item_id', $sheetQaItemIds);

                $sheetApplicable = $sheetStatuses->where('applicable', true)->count();
                $sheetConfirmed  = $sheetStatuses->where('confirmed', true)->count();

                $sheet->progress = $sheetApplicable > 0
                    ? round(($sheetConfirmed / $sheetApplicable) * 100, 1)
                    : 0;
            }
        }
    }

    return $projects;
}

public function getProjectProgress($projectId = null)
{
    // Load project structure
    $projectsQuery = Project::with(['phases.sheets']);

    if ($projectId) {
        $projectsQuery->where('id', $projectId);
    }

    $projects = $projectsQuery->get();

    if ($projects->isEmpty()) {
        return collect();
    }

    // Load QA items grouped by sheet
    $qaItems = QAItem::select('id', 'sheet_id')
        ->get()
        ->groupBy('sheet_id');

    // Load project QA statuses
    $statuses = ProjectQAItemStatus::select(
        'project_id',
        'qa_item_id',
        'applicable',
        'incorporated',
        'confirmed'
    )->get()->groupBy('project_id');


    // =============================
    // PROGRESS CALCULATION
    // =============================
    foreach ($projects as $project) {

        // ===== PROJECT LEVEL =====
        $projectSheetIds = $project->phases
            ->flatMap(fn($p) => $p->sheets->pluck('id'));

        $projectQaItemIds = $projectSheetIds->flatMap(
            fn($sheetId) => $qaItems->get($sheetId, collect())->pluck('id')
        );

        $projectStatuses = $statuses
            ->get($project->id, collect())
            ->whereIn('qa_item_id', $projectQaItemIds);

        $projectApplicable   = $projectStatuses->where('applicable', true)->count();
        $projectConfirmed    = $projectStatuses->where('confirmed', true)->count();
        $projectIncorporated = $projectStatuses->where('incorporated', true)->count();

        // You can choose the % logic here:
        // I used A vs C (business rule)
        $project->progress = $projectApplicable > 0
            ? round(($projectConfirmed / $projectApplicable) * 100, 1)
            : 0;


        // ===== PHASE LEVEL =====
        foreach ($project->phases as $phase) {

            $phaseSheetIds = $phase->sheets->pluck('id');

            $phaseQaItemIds = $phaseSheetIds->flatMap(
                fn($sheetId) => $qaItems->get($sheetId, collect())->pluck('id')
            );

            $phaseStatuses = $projectStatuses->whereIn('qa_item_id', $phaseQaItemIds);

            $phaseApplicable   = $phaseStatuses->where('applicable', true)->count();
            $phaseConfirmed    = $phaseStatuses->where('confirmed', true)->count();
            $phaseIncorporated = $phaseStatuses->where('incorporated', true)->count();

            $phase->progress = $phaseApplicable > 0
                ? round(($phaseConfirmed / $phaseApplicable) * 100, 1)
                : 0;


            // ===== SHEET LEVEL =====
            foreach ($phase->sheets as $sheet) {

                $sheetQaItemIds = $qaItems->get($sheet->id, collect())->pluck('id');

                $sheetStatuses = $projectStatuses->whereIn('qa_item_id', $sheetQaItemIds);

                $sheetApplicable   = $sheetStatuses->where('applicable', true)->count();
                $sheetIncorporated = $sheetStatuses->where('incorporated', true)->count();
                $sheetConfirmed    = $sheetStatuses->where('confirmed', true)->count();

                $sheet->progress = $sheetApplicable > 0
                    ? round(($sheetConfirmed / $sheetApplicable) * 100, 1)
                    : 0;
            }
        }
    }

    return $projects;
}

public function ajaxProjectProgress(Request $request)
{
    $projectId = $request->get('project_id');

    if (!$projectId) {
        $latestProject = Project::latest('id')->first();
        if (!$latestProject) {
            return response()->json(['project' => null]);
        }
        $projectId = $latestProject->id;
    }

    // Get full progress tree using your existing optimized method
    $projects = $this->getProjectProgress($projectId);

    $project = $projects->first();

    if (!$project) {
        return response()->json(['project' => null]);
    }

    return response()->json([
        'project' => [
            'id'       => $project->id,
            'name'     => $project->name,
            'progress' => $project->progress,
            'phases'   => $project->phases->map(function ($p) {
                return [
                    'id'       => $p->id,
                    'type'     => $p->type,
                    'progress' => $p->progress,
                    'sheets'   => $p->sheets->map(function ($s) {
                        return [
                            'id'       => $s->id,
                            'number'   => $s->number,
                            'title'    => $s->title,
                            'progress' => $s->progress
                        ];
                    })
                ];
            }),
        ]
    ]);
}


public function qaTrend1()
{
    
    // Last 30 days
    $start = Carbon::now()->subDays(30)->startOfDay();
    $end   = Carbon::now()->endOfDay();

    // CREATED per day
    $created = QAItem::whereBetween('created_at', [$start, $end])
        ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')
        ->orderBy('day')
        ->pluck('count', 'day');

    // RESOLVED per day
    $resolved = QAItem::where('status', 'resolved')
        ->whereBetween('updated_at', [$start, $end])
        ->select(DB::raw('DATE(updated_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')
        ->orderBy('day')
        ->pluck('count', 'day');

    // CLOSED or VERIFIED per day
    $closed = QAItem::whereIn('status', ['closed', 'verified'])
        ->whereBetween('updated_at', [$start, $end])
        ->select(DB::raw('DATE(updated_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')
        ->orderBy('day')
        ->pluck('count', 'day');

    // Build a consistent date range (all 30 days)
    $days = collect();
    for ($i = 0; $i < 30; $i++) {
        $date = Carbon::now()->subDays(29 - $i)->format('Y-m-d');
        $days->push($date);
    }

    // Normalize dataset (missing dates become 0)
    $chartData = [
        'labels' => $days,
        'created' => $days->map(fn($d) => $created[$d] ?? 0),
        'resolved' => $days->map(fn($d) => $resolved[$d] ?? 0),
        'closed' => $days->map(fn($d) => $closed[$d] ?? 0),
    ];

    return response()->json($chartData);
}


public function qaTrend(Request $request)
{
    // Show only last 5 days to verify data appears
    $days = $request->days ?? 30;

    $start = Carbon::now()->subDays($days)->startOfDay();
    $end   = Carbon::now()->endOfDay();

    // -----------------------------
    // CREATED ITEMS
    // -----------------------------
    $created = ProjectQAItemStatus::whereBetween('created_at', [$start, $end])
        ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')->orderBy('day')
        ->pluck('count', 'day')
        ->toArray();

    // -----------------------------
    // RESOLVED = confirmed = 1
    // -----------------------------
    $resolved = ProjectQAItemStatus::where('confirmed', 1)
        ->whereBetween('updated_at', [$start, $end])
        ->select(DB::raw('DATE(updated_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')->orderBy('day')
        ->pluck('count', 'day')
        ->toArray();

    // -----------------------------
    // CLOSED = three checkboxes = 1
    // -----------------------------
    $closed = ProjectQAItemStatus::where([
            ['applicable', 1],
            ['incorporated', 1],
            ['confirmed', 1]
        ])
        ->whereBetween('updated_at', [$start, $end])
        ->select(DB::raw('DATE(updated_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')->orderBy('day')
        ->pluck('count', 'day')
        ->toArray();

    // Build daily labels
    $period = new \DatePeriod(
        $start,
        new \DateInterval('P1D'),
        $end->copy()->addDay()
    );

    $labels = [];
    foreach ($period as $date) {
        $labels[] = $date->format('Y-m-d');
    }

    // Normalize arrays
    $createdArr  = [];
    $resolvedArr = [];
    $closedArr   = [];

    foreach ($labels as $day) {
        $createdArr[]  = $created[$day]  ?? 0;
        $resolvedArr[] = $resolved[$day] ?? 0;
        $closedArr[]   = $closed[$day]   ?? 0;
    }

    return response()->json([
        'labels'   => $labels,
        'created'  => $createdArr,
        'resolved' => $resolvedArr,
        'closed'   => $closedArr,
    ]);
}



public function chartData($projectId)
{
    $stats = QaDailyStat::where('project_id', $projectId)
        ->orderBy('date')
        ->get();

    return response()->json([
        'dates'  => $stats->pluck('date'),
        'open'   => $stats->pluck('open_count'),
        'progress' => $stats->pluck('resolved_count'),
        'closed' => $stats->pluck('closed_count'),
    ]);
}


// new analytics topics for 7/12
public function sheetStatusHeatmap(Request $request)
{
    $projectId = $request->get('project_id');
    $phaseId   = $request->get('phase_id');

    $statuses = ['open', 'in_progress', 'needs_info', 'resolved', 'verified', 'closed'];

    // --- BASE QUERY ---
    $sheetsQuery = Sheet::with([
        'phase.project',
        'qaItems.projectStatus' => function ($q) use ($projectId) {
            if ($projectId) {
                $q->where('project_id', $projectId);
            }
        }
    ]);

    // --- FILTER BY PROJECT ---
    if ($projectId) {
        $sheetsQuery->whereHas('phase', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });
    }

    // --- FILTER BY PHASE ---
    if ($phaseId) {
        $sheetsQuery->where('phase_id', $phaseId);
    }

   
    $sheets = $sheetsQuery->get();

    $rows = $sheets->map(function ($sheet) use ($statuses) {

        $row = [
            'sheet_id'    => $sheet->id,
            'sheet_label' => $sheet->number . ' - ' . $sheet->title,
            'phase'       => optional($sheet->phase)->type,
            'project'     => optional(optional($sheet->phase)->project)->name,
        ];

        foreach ($statuses as $status)
            $row[$status] = 0;

        // Count A/I/C
        $a = $i = $c = 0;

        foreach ($sheet->qaItems as $item) {

            $ps = $item->projectStatus;

            if (!$ps) continue;

            if ($ps->applicable)   $a++;
            if ($ps->incorporated) $i++;
            if ($ps->confirmed)    $c++;

            $derived = $ps->derived_status ?? 'open';

            if (isset($row[$derived])) {
                $row[$derived]++;
            }
        }

        $row['a_count'] = $a;
        $row['i_count'] = $i;
        $row['c_count'] = $c;

        $row['total'] = array_sum(array_intersect_key($row, array_flip($statuses)));

        return $row;

    })->filter(fn($r) => $r['total'] > 0)->values();

    return response()->json([
        'statuses' => $statuses,
        'rows'     => $rows,
        'aic'      => ['applicable', 'incorporated', 'confirmed']
    ]);
}

public function phaseGateAnalytics(Request $request)
{
    $projectId = $request->integer('project_id'); // null if not sent

    // 1) phases + relations (with filtering)
    $phases = Phase::with(['project','sheets' => fn($q) => $q->select('id','phase_id')])
        ->when($projectId, fn($q)=>$q->where('project_id',$projectId))
        ->get();

    if ($phases->isEmpty()) {
        return response()->json([]); // لا يوجد phases للعرض
    }

    // 2) Build fast lookups
    $sheetIds = $phases->flatMap(fn($p)=>$p->sheets->pluck('id'))->unique()->values();
    $qaItems  = QAItem::select('id','sheet_id','severity')
                  ->whereIn('sheet_id', $sheetIds)->get();
    $qaBySheet = $qaItems->groupBy('sheet_id');
    $qaById    = $qaItems->keyBy('id');

    // احصر المشاريع المعنية فقط
    $projectIds = $phases->pluck('project_id')->unique()->values();

    $statuses = ProjectQAItemStatus::select('project_id','qa_item_id','applicable','incorporated','confirmed','comments')
                  ->whereIn('project_id', $projectIds)
                  ->get()
                  ->groupBy('project_id');

    $data = [];

    foreach ($phases as $phase) {
        $projId   = $phase->project_id;
        $sheetIds = $phase->sheets->pluck('id');

        // QA IDs داخل الشيتات
        $phaseQaItemIds = $sheetIds->flatMap(
            fn($sid)=> $qaBySheet->get($sid, collect())->pluck('id')
        )->unique()->values();

        // حالات هذا المشروع لعناصر هذه الـ phase
        $phaseStatuses = $statuses->get($projId, collect())
            ->whereIn('qa_item_id', $phaseQaItemIds);

        // أحسب المؤشرات (0/1 صريح)
        $totalApplicable = $phaseStatuses->where('applicable', 1)->count();
        $completed = $phaseStatuses->filter(fn($s)=> (int)$s->applicable===1 && (int)$s->confirmed===1)->count();
        $blocking  = $phaseStatuses->filter(fn($s)=> (int)$s->applicable===1 && (int)$s->confirmed===0)->count();
        $critical  = $phaseStatuses->filter(function($s) use ($qaById){
                        $sev = strtolower($qaById[$s->qa_item_id]->severity ?? '');
                        return (int)$s->applicable===1 && (int)$s->confirmed===0
                               && in_array($sev, ['critical','high']);
                     })->count();

        $percent = $totalApplicable > 0 ? round(($completed / $totalApplicable) * 100, 1) : 0.0;

        // تقدير ETA بسيط
        $capacityPerDay = 10;
        $etaDays = $blocking > 0 ? (int) ceil($blocking / $capacityPerDay) : 0;
        $etaDate = $etaDays ? now()->addDays($etaDays)->toDateString() : null;

        $phaseStatus = match (true) {
            $percent == 100 => 'CLOSED',
            $blocking == 0  => 'READY_FOR_SIGNOFF',
            $percent >= 50  => 'IN_REVIEW',
            default         => 'CHANGES_REQUIRED',
        };

        $data[] = [
            'phase_id'          => $phase->id,
            'phase_type'        => $phase->type,
            'phase_status'      => $phaseStatus,
            'project'           => $phase->project->name,
            'total_items'       => $totalApplicable,
            'completed'         => $completed,
            'blocking'          => $blocking,
            'critical_blocking' => $critical,
            'percent_complete'  => $percent,
            'eta_signoff'       => $etaDate,
        ];
    }

    return response()->json($data);
}


public function phaseGateAnalytics1(Request $request)
{
    $projectId = $request->get('project_id');

    // Load phases + sheets
    $phasesQuery = Phase::with(['project', 'sheets']);
    if ($projectId) {
        $phasesQuery->where('project_id', $projectId);
    }
    $phases = $phasesQuery->get();

    // Load QA Items by sheet
    $qaItems = QAItem::select('id', 'sheet_id')->get()->groupBy('sheet_id');

    // Load project statuses (A/I/C)
    $statuses = ProjectQAItemStatus::select(
        'project_id',
        'qa_item_id',
        'applicable',
        'incorporated',
        'confirmed',
        'comments'
    )->get()->groupBy('project_id');

    $data = [];

    foreach ($phases as $phase) {

        $projId = $phase->project_id;

        // Sheet IDs inside this phase
        $sheetIds = $phase->sheets->pluck('id');

        // All QA items inside sheets
        $phaseQaItemIds = $sheetIds->flatMap(
            fn($sheetId) => $qaItems->get($sheetId, collect())->pluck('id')
        );

        // Project statuses for this specific phase
        $phaseStatuses = $statuses->get($projId, collect())
            ->whereIn('qa_item_id', $phaseQaItemIds);

        // Logic based on A/I/C
   /*     $totalApplicable = $phaseStatuses->where('applicable', true)->count();
        $totalConfirmed  = $phaseStatuses->where('confirmed', true)->count();

        // Completed = confirmed
        $completed = $totalConfirmed;
        */
$completed = $phaseStatuses
    ->filter(fn($s) => (int)$s->applicable === 1 && (int)$s->confirmed === 1)
    ->count(); // ✅


        // Blocking = Applicable but NOT Confirmed
        $blocking = $phaseStatuses
            ->where('applicable', true)
            ->where('confirmed', false)
            ->count();

        // Critical blocking = items with comments containing "critical"
        // (optional logic, adjust later)
        $critical = $phaseStatuses
            ->filter(fn($s) => str_contains(strtolower($s->comments ?? ''), 'critical'))
            ->count();

        // Progress %
        $percent = $totalApplicable > 0
            ? round(($completed / $totalApplicable) * 100, 1)
            : 0;

        // ETA estimation
        $capacityPerDay = 10;
        $etaDays = $blocking > 0 ? ceil($blocking / $capacityPerDay) : 0;
        $etaDate = $etaDays > 0 ? now()->addDays($etaDays)->toDateString() : null;

        // Overall Phase Status (example logic)
        $phaseStatus = match (true) {
            $percent == 100 => 'CLOSED',
            $blocking == 0   => 'READY_FOR_SIGNOFF',
            $percent >= 50   => 'IN_REVIEW',
            default          => 'CHANGES_REQUIRED'
        };

        $data[] = [
            'phase_id'          => $phase->id,
            'phase_type'        => $phase->type,
            'phase_status'      => $phaseStatus,
            'project'           => $phase->project->name,
            'total_items'       => $totalApplicable,
            'completed'         => $completed,
            'blocking'          => $blocking,
            'critical_blocking' => $critical,
            'percent_complete'  => $percent,
            'eta_signoff'       => $etaDate,
        ];
    }

    return response()->json($data);
}


public function slaAnalytics(Request $request)
{
    $now = Carbon::now();

    // Only items that are not closed
    $activeStatuses = ['open', 'in_progress', 'needs_info'];

    // Load QA items WITH project-level status
    $items = QaItem::whereIn('status', $activeStatuses)
        ->with('projectStatus')   // <-- important
        ->get();

    $totalActive = $items->count();

    /**
     * ---------------------------------------
     * 1) Determine Overdue items
     * ---------------------------------------
     * Overdue = projectStatus->due_date < today
     */
    $overdue = $items->filter(function ($item) use ($now) {
        $dueDate = optional($item->projectStatus)->due_date;

        return $dueDate && Carbon::parse($dueDate)->lt($now);
    });

    /**
     * ---------------------------------------
     * 2) Calculate Average Age (in days)
     * ---------------------------------------
     */
    $avgAgeDays = $items->count()
        ? round($items->avg(fn ($i) => $i->created_at->diffInDays($now)), 1)
        : 0;

    /**
     * ---------------------------------------
     * 3) Age Buckets (based on created_at age)
     * ---------------------------------------
     */
    $buckets = [
        '0_3'      => 0,
        '4_7'      => 0,
        '8_14'     => 0,
        '15_plus'  => 0,
    ];

    foreach ($items as $item) {
        $age = $item->created_at->diffInDays($now);

        if ($age <= 3)         $buckets['0_3']++;
        elseif ($age <= 7)    $buckets['4_7']++;
        elseif ($age <= 14)   $buckets['8_14']++;
        else                  $buckets['15_plus']++;
    }

    /**
     * ---------------------------------------
     * 4) Overdue Breakdown by Severity
     * ---------------------------------------
     */
    $severityOverdue = [
        'critical' => 0,
        'high'     => 0,
        'medium'   => 0,
        'low'      => 0,
    ];

    foreach ($overdue as $item) {
        $sev = strtolower($item->severity ?? 'medium');

        if (!isset($severityOverdue[$sev])) {
            $sev = 'medium'; // fallback
        }

        $severityOverdue[$sev]++;
    }

    /**
     * ---------------------------------------
     * FINAL RESPONSE
     * ---------------------------------------
     */
    return response()->json([
        'total_active'    => $totalActive,
        'overdue_count'   => $overdue->count(),
        'avg_age_days'    => $avgAgeDays,
        'age_buckets'     => $buckets,
        'severity_overdue'=> $severityOverdue,
    ]);
}




public function qaTrendProject(Request $request)
{
    $projectId = $request->project_id;
    $phaseId   = $request->phase_id;

    $days = 30;
    $start = Carbon::now()->subDays($days)->startOfDay();
    $end   = Carbon::now()->endOfDay();

    // Base query
    $baseQuery = QaItem::whereBetween('created_at', [$start, $end])
        ->with(['sheet.phase']);

    if ($projectId) {
        $baseQuery->whereHas('sheet.phase', function ($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });
    }

    if ($phaseId) {
        $baseQuery->whereHas('sheet', function ($q) use ($phaseId) {
            $q->where('phase_id', $phaseId);
        });
    }

    // CREATED
    $created = (clone $baseQuery)
        ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')
        ->pluck('count', 'day')
        ->toArray();

    // RESOLVED
    $resolved = (clone $baseQuery)
        ->where('status', 'resolved')
        ->select(DB::raw('DATE(updated_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')
        ->pluck('count', 'day')
        ->toArray();

    // CLOSED / VERIFIED
    $closed = (clone $baseQuery)
        ->whereIn('status', ['closed', 'verified'])
        ->select(DB::raw('DATE(updated_at) as day'), DB::raw('COUNT(*) as count'))
        ->groupBy('day')
        ->pluck('count', 'day')
        ->toArray();

    // Build continuous daily labels
    $labels = [];
    for ($i = 0; $i < $days; $i++) {
        $labels[] = Carbon::now()->subDays($days - 1 - $i)->format('Y-m-d');
    }

    return response()->json([
        'labels'   => $labels,
        'created'  => array_map(fn($d) => $created[$d] ?? 0, $labels),
        'resolved' => array_map(fn($d) => $resolved[$d] ?? 0, $labels),
        'closed'   => array_map(fn($d) => $closed[$d] ?? 0, $labels),
    ]);
}


public function phaseBurndown(Request $request)
{
    $phaseId = $request->get('phase_id');
    if (!$phaseId) return response()->json([]);

    $phase = Phase::with('sheets.qaItems.projectStatus')->find($phaseId);
    if (!$phase) return response()->json([]);

    $today = now();
    $start = now()->subDays(29)->startOfDay();

    $dates = collect();
    for ($i = 0; $i < 30; $i++) {
        $dates->push($start->copy()->addDays($i)->format('Y-m-d'));
    }

    $dailyRemaining = [];

    // Total QA items in this phase
    $allStatuses = collect();
    foreach ($phase->sheets as $sheet) {
        foreach ($sheet->qaItems as $item) {
            foreach ($item->projectStatus as $ps) {
                $allStatuses->push($ps);
            }
        }
    }

    $totalItems = $allStatuses->count();

    foreach ($dates as $date) {

        // confirmed until today?
        $confirmed = $allStatuses->filter(function ($ps) use ($date) {
            return $ps->confirmed == 1 && $ps->updated_at <= $date . ' 23:59:59';
        })->count();

        $remaining = $totalItems - $confirmed;

        $dailyRemaining[] = $remaining;
    }

    return response()->json([
        'labels'    => $dates,
        'remaining' => $dailyRemaining,
        'total'     => $totalItems
    ]);
}


public function getQaItems(Request $req)
{
    $sheetId = $req->sheet_id;
    $status  = $req->status;
    $page    = $req->page ?? 1;
    $perPage = 20;

    // Load all items with relationships
    $query = QaItem::where('sheet_id', $sheetId)
        ->with(['sheet', 'assigneeUser', 'projectStatus'])
        ->get()
        ->map(function ($item) {

            // FIX: ensure projectStatus is ONE record only
            $item->projectStatus =
                $item->projectStatus instanceof \Illuminate\Support\Collection
                    ? $item->projectStatus->first()
                    : $item->projectStatus;

            return $item;
        })
        ->unique('id')
        ->values();

    // FILTER
    $filtered = $query->filter(function ($item) use ($status) {

        $ps = $item->projectStatus;

        // Handle A / I / C special indicators
        if (in_array($status, ['A', 'I', 'C'])) {
            if (!$ps) return false;

            return match($status) {
                'A' => $ps->applicable == 1,
                'I' => $ps->incorporated == 1,
                'C' => $ps->confirmed == 1,
            };
        }

        // Normal derived status
        $derived = $ps->derived_status ?? $item->status;

        return $derived === $status;
    });

    // TOTAL COUNT
    $total = $filtered->count();

    // PAGINATE
    $items = $filtered
        ->slice(($page - 1) * $perPage, $perPage)
        ->map(function ($i) {

            $ps = $i->projectStatus;

            return [
                'id'         => $i->id,
                'description'=> $i->item_description,
                'sheet_label'=> $i->sheet->number . ' - ' . $i->sheet->title,
                'status'     => $ps->derived_status ?? $i->status,
                'assignee'   => $i->assigneeUser->name ?? null,
                'due_date'   => $ps->due_date ?? null,
                'created_at' => $i->created_at->format('Y-m-d'),
            ];
        })
        ->values();

    return response()->json([
        'items'     => $items,
        'total'     => $total,
        'page'      => $page,
        'per_page'  => $perPage
    ]);
}



}
