<?php

namespace App\Http\Controllers;

use App\Models\Phase;
use Illuminate\Http\Request;

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

}
