<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Phase;
use App\Models\Sheet;
use App\Models\QaItem;
use App\Models\User;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
   public function index()
{
    // Summary Data
    $data = [
        'projects_count' => Project::count(),
        'phases_count' => Phase::count(),
        'sheets_count' => Sheet::count(),
        'qa_items_count' => QaItem::count(),

        'qa_open'     => QaItem::where('status', 'open')->count(),
        'qa_pending'  => QaItem::where('status', 'pending')->count(),
        'qa_resolved' => QaItem::where('status', 'resolved')->count(),
        'qa_closed'   => QaItem::where('status', 'closed')->count(),

        'qa_critical' => QaItem::where('severity', 'critical')->count(),
        'qa_high'     => QaItem::where('severity', 'high')->count(),
        'qa_medium'   => QaItem::where('severity', 'medium')->count(),
        'qa_low'      => QaItem::where('severity', 'low')->count(),

        'reviewer_load' => User::withCount('qaItemsAssigned')
            ->having('qa_items_assigned_count', '>', 0)
            ->orderBy('qa_items_assigned_count', 'desc')
            ->take(5)
            ->get()
    ];

    // Optimized Project Loading
/*
$projects = Project::withCount(['phases', 'sheets', 'qaItems'])
    ->with([
        'phases:id,project_id,name',
        'sheets:id,phase_id,sheet_number,title',
        'qaItems:id,sheet_id,item_number'
    ])
    ->get(['id', 'name', 'description']);
*/
$projects = Project::withCount(['phases', 'sheets', 'qaItems'])
    ->with(['phases', 'sheets'])
    ->get();


    return view('dashboard.index', compact('data', 'projects'));
}

}
