<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function index(Request $request)
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
