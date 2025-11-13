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
    $data = [
        'projects_count' => Project::count(),
        'phases_count' => Phase::count(),
        'sheets_count' => Sheet::count(),
        'qa_items_count' => QaItem::count(),

        // Status Breakdown
        'qa_open'     => QaItem::where('status', 'open')->count(),
        'qa_pending'  => QaItem::where('status', 'pending')->count(),
        'qa_resolved' => QaItem::where('status', 'resolved')->count(),
        'qa_closed'   => QaItem::where('status', 'closed')->count(),
    ];

    // Top 5 Reviewers with most assigned QA Items
   $data['reviewer_load'] = User::withCount('qaItemsAssigned')
    ->having('qa_items_assigned_count', '>', 0)
    ->orderBy('qa_items_assigned_count', 'desc')
    ->take(5)
    ->get();



    return view('dashboard.index', compact('data'));
}

}
