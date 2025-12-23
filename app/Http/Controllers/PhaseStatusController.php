<?php

namespace App\Http\Controllers;

use App\Models\Phase;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class PhaseStatusController extends Controller
{
public function update(Request $request, Phase $phase)
{
    $request->validate([
        'status' => 'required|string|in:' . implode(',', Phase::statuses()),
    ]);

    $user = auth()->user();
    $newStatus = $request->status;

    // === RULE 1: ENGINEER → NO ACCESS ===
    if ($user->role->name === 'Engineer') {
        return back()->with('error', 'Engineers cannot update phase status.');
    }

    // === RULE 2: REVIEWER → LIMITED ACCESS ===
    if ($user->role->name === 'Reviewer') {
        $allowed = ['IN_REVIEW', 'CHANGES_REQUIRED'];

        if (!in_array($newStatus, $allowed)) {
            return back()->with('error', 'Reviewers can only move phases to review or changes-required.');
        }
    }

    // === RULE 3: PM & ADMIN → FULL ACCESS ===
    // No need to block anything

    // === Normal status rules (transition map) ===
    if (! $phase->canTransitionTo($newStatus)) {
        return back()->with('error', 'Invalid phase status transition');
    }

    $phase->status = $newStatus;
    $phase->save();

    return back()->with('success', 'Phase status updated.');
}


public function updateStatus(Request $request, Phase $phase)
{
    $request->validate([
        'status' => 'required|string',
    ]);

    $user = auth()->user();
    $newStatus = $request->status;
    $oldStatus = $phase->status; // ✅ TAKE OLD STATUS BEFORE UPDATE

    // ✅ Role restrictions
    if ($newStatus === 'ready_for_signoff' && !$user->hasRole(['PM', 'Admin'])) {
        return back()->with('error', 'Only PM/Admin can set phase to Ready for Signoff.');
    }

    if ($newStatus === 'closed' && !$user->hasRole(['PM', 'Admin'])) {
        return back()->with('error', 'Only PM/Admin can close the phase.');
    }

    // ✅ Gate rules for READY_FOR_SIGNOFF
    if ($newStatus === 'ready_for_signoff') {

        $openCount = $phase->qaItems()
            ->whereIn('qa_items.status', ['open', 'in_progress', 'needs_info'])
            ->count();

        $criticalNotVerified = $phase->qaItems()
            ->where('qa_items.severity', 'critical')
            ->whereNotIn('qa_items.status', ['verified', 'closed'])
            ->count();

        if ($openCount > 0 || $criticalNotVerified > 0) {
            return back()->with(
                'error',
                "You can’t mark this phase as Ready for Signoff yet.
                 Please resolve: {$openCount} open/in progress/needs info items, and {$criticalNotVerified} critical items still not verified."
            );
        }
    }

    // ✅ Gate rules for CLOSED
    if ($newStatus === 'closed') {

        if ($oldStatus !== 'ready_for_signoff') {
            return back()->with('error', 'Phase must be Ready for Signoff before closing.');
        }

        $blockingCount = $phase->qaItems()
            ->whereIn('qa_items.status', ['open', 'in_progress', 'needs_info', 'resolved'])
            ->count();

        if ($blockingCount > 0) {
            return back()->with('error', "Phase cannot be closed. {$blockingCount} items are not verified/closed yet.");
        }
    }

    // ✅ 1) Apply update ONCE
    $phase->update(['status' => $newStatus]);

    // ✅ 2) Log (arrays, not json_encode)
    ActivityLog::create([
        'project_id' => $phase->project_id,
        'phase_id' => $phase->id,
        'sheet_id' => null,
        'qa_item_id' => null,
        'user_id' => auth()->id(),
        'action_type' => 'phase_status_changed',
        'old_value' => ['status' => $oldStatus],
        'new_value' => ['status' => $newStatus],
        'note' => $request->input('comment') ?? null,
    ]);

    return back()->with('success', 'Phase status updated successfully.');
}


}
