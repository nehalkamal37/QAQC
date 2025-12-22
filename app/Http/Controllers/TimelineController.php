<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Models\QaItem;

class TimelineController extends Controller
{

    public function index(Request $request)
{
    $user        = $request->user;
    $action      = $request->action;
    $project     = $request->project;
    $severity    = $request->severity;
    $search      = $request->search;
    $item_id     = $request->item_id;

    // New Filters
    $assignedTo  = $request->assigned_to;
    $applicable  = $request->applicable;
    $incorporated = $request->incorporated;
    $confirmed   = $request->confirmed;
    $dueFrom     = $request->due_from;
    $dueTo       = $request->due_to;

  //  $logs = ActivityLog::with(['user','project','phase','sheet','item'])
$logs = ActivityLog::query()
    ->leftJoin('project_qa_item_statuses as pq', function ($j) {
        $j->on('pq.qa_item_id', '=', 'activity_logs.qa_item_id')
          ->on('pq.project_id', '=', 'activity_logs.project_id');
    })
    ->select('activity_logs.*')
    ->with(['user','project','phase','sheet','item'])


// ...


->when($assignedTo, function($q) use ($assignedTo) {
    $q->where(function($w) use ($assignedTo) {
        $w->whereRaw(
            "JSON_UNQUOTE(JSON_EXTRACT(activity_logs.new_value, '$.assigned_to')) = ?",
            [$assignedTo]
        )
        ->orWhere('pq.assigned_to', $assignedTo);
    });
})

        // User
        ->when($user, fn($q) => $q->where('user_id', $user))

        // Action Type
        ->when($action, fn($q) => $q->where('action_type', $action))

        // Project
        ->when($project, fn($q) => $q->where('project_id', $project))

        // QA Item ID
        ->when($item_id, fn($q) => $q->where('qa_item_id', $item_id))

        // Severity (belongs to QA Item)
        ->when($severity, fn($q) =>
            $q->whereHas('item', fn($i) => $i->where('severity', $severity))
        )

        // 🔥 New: Assigned To filter (project-level)
     /*   ->when($assignedTo, fn($q) =>
            $q->whereJsonContains('new_value->assigned_to', $assignedTo)
        )
*/
        // 🔥 New: Applicable filter
        ->when(!is_null($applicable), fn($q) =>
            $q->whereJsonContains('new_value->applicable', (int)$applicable)
        )


        // 🔥 New: Incorporated filter
        ->when(!is_null($incorporated), fn($q) =>
            $q->whereJsonContains('new_value->incorporated', (int)$incorporated)
        )

        // 🔥 New: Confirmed filter
        ->when(!is_null($confirmed), fn($q) =>
            $q->whereJsonContains('new_value->confirmed', (int)$confirmed)
        )


        
        // 🔥 New: Due date range
        ->when($dueFrom, fn($q) =>
            $q->where('new_value->due_date', '>=', $dueFrom)
        )
        ->when($dueTo, fn($q) =>
            $q->where('new_value->due_date', '<=', $dueTo)
        )

        // 🔥 Extended Search
        ->when($search, fn($q) =>
            $q->where(function($sub) use ($search) {
                $sub->where('note','like',"%$search%")
                    ->orWhereHas('item', fn($i) =>
                        $i->where('item_description','like',"%$search%")
                    )
                    ->orWhereJsonContains('new_value->comments', $search)
                //    ->orWhereJsonContains('new_value->status', $search)
                    // before: only new_value
->orWhereJsonContains('new_value->status', $search)

// after: include old_value and fallback LIKE against the raw JSON blob
->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(activity_logs.new_value, '$.status')) LIKE ?", ["%{$search}%"])
->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(activity_logs.old_value,  '$.status')) LIKE ?", ["%{$search}%"])
->orWhere('activity_logs.new_value', 'like', "%{$search}%") // fallback
->orWhere('activity_logs.old_value', 'like', "%{$search}%") // fallback

                    ->orWhereJsonContains('new_value->assigned_to', $search)
                    ->orWhereJsonContains('new_value->due_date', $search)
                    ->orWhereJsonContains('new_value->applicable', $search)
                    ->orWhereJsonContains('new_value->incorporated', $search)
                    ->orWhereJsonContains('new_value->confirmed', $search);
            })
        )

        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->withQueryString();


    


    return view('timeline.index', compact(
        'logs','user','action','project','severity','search','item_id',
        'assignedTo','applicable','incorporated','confirmed','dueFrom','dueTo'
    ));


}

    public function index1(Request $request)
    {
        $user      = $request->user;
        $action    = $request->action;
        $project   = $request->project;
        $severity  = $request->severity;
        $search    = $request->search;
        $item_id   = $request->item_id;

        $logs = ActivityLog::with(['user','project','phase','sheet','item'])
            ->when($user, fn($q) => $q->where('user_id', $user))
            ->when($action, fn($q) => $q->where('action_type', $action))
            ->when($project, fn($q) => $q->where('project_id', $project))
            ->when($severity, fn($q) =>
                $q->whereHas('item', fn($i) => $i->where('severity', $severity))
            )
            ->when($item_id, fn($q) =>
                $q->where('qa_item_id', $item_id)
            )
            ->when($search, fn($q) =>
                $q->where(function($inner) use ($search) {
                    $inner->where('note','like',"%$search%")
                          ->orWhereJsonContains('new_value->status', $search)
                          ->orWhereJsonContains('old_value->status', $search);
                })
            )
            ->orderBy('created_at','desc')
            ->paginate(25)
            ->withQueryString();

        return view('timeline.index', compact(
            'logs', 'user', 'action', 'project', 'severity', 'search', 'item_id'
        ));
    }



    public function destroy($id)
{
    $log = ActivityLog::findOrFail($id);
    $log->delete();

    return redirect()->back()->with('success', 'Activity log deleted successfully.');
}

}
