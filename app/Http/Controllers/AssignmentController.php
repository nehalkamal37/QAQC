<?php
// app/Http/Controllers/AssignmentController.php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Phase;
use App\Services\NotificationService;
use App\Models\QaItem;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Assignment::with(['user', 'project', 'phase'])
            ->active();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $assignments = $query->get();
$projects = Project::all();
$phases = Phase::all();

    

        return view('assignments.index', compact('assignments', 'projects','phases'));
    }

    public function create()
    {
        $users = User::all();
        $projects = Project::all();
        $roles = [
            'pm' => 'Project Manager',
            'senior_reviewer' => 'Senior Reviewer',
            'engineer' => 'Engineer',
            'eit' => 'EIT',
            'night_vision' => 'Night Vision'
        ];

        return view('assignments.create', compact('users', 'projects', 'roles'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'project_id' => 'required|exists:projects,id',
        'phase_id' => 'nullable|exists:phases,id',
        'role' => 'required|in:pm,senior_reviewer,engineer,eit,night_vision',
        'notes' => 'nullable|string'
    ]);

    // Check for duplicate active assignments
    $existing = Assignment::where('user_id', $validated['user_id'])
        ->where('project_id', $validated['project_id'])
        ->where('role', $validated['role'])
        ->active()
        ->exists();

    if ($existing) {
        return back()->with('error', 'User already has this role assignment for the project.');
    }

    try {
        DB::transaction(function () use ($validated) {
            // Deactivate any existing assignment for same user/project/role
            Assignment::where('user_id', $validated['user_id'])
                ->where('project_id', $validated['project_id'])
                ->where('role', $validated['role'])
                ->update(['is_active' => false, 'removed_at' => now()]);

            // Create new assignment and get the instance
            $assignment = Assignment::create([
                'user_id' => $validated['user_id'],
                'project_id' => $validated['project_id'],
                'phase_id' => $validated['phase_id'],
                'role' => $validated['role'],
                'assigned_at' => now(),
                'notes' => $validated['notes']
            ]);

            // Load relationships for notification
            $assignment->load(['project', 'user']);

            // Send notification AFTER assignment is created
            NotificationService::notifyAssignment(
                $assignment->user_id,
                $assignment->project->name,
                $assignment->role,
                auth()->user()->name
            );
        });

        return redirect()->route('assignments.index')
            ->with('success', 'User assigned successfully.');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to assign user: ' . $e->getMessage());
    }
}


        /*
    // Missing: Prevent conflicting assignments
// Suggested addition to AssignmentController:
public function store(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'project_id' => 'required|exists:projects,id',
        'phase_id' => 'nullable|exists:phases,id',
        'role' => 'required|in:pm,senior_reviewer,engineer,eit,night_vision',
        'notes' => 'nullable|string|max:500'
    ]);

    // Check for duplicate active assignments
    $existing = Assignment::where('user_id', $validated['user_id'])
        ->where('project_id', $validated['project_id'])
        ->where('role', $validated['role'])
        ->active()
        ->exists();

    if ($existing) {
        return back()->with('error', 'User already has this role assignment for the project.');
    }
    
    // Rest of assignment logic...
}
*/
    public function destroy(Assignment $assignment)
    {
        $assignment->deactivate();

        return redirect()->route('assignments.index')
            ->with('success', 'Assignment removed successfully.');
    }

    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'role' => 'required|in:pm,senior_reviewer,engineer,eit,night_vision'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                foreach ($validated['user_ids'] as $userId) {
                    // Deactivate existing assignment
                    Assignment::where('user_id', $userId)
                        ->where('project_id', $validated['project_id'])
                        ->where('role', $validated['role'])
                        ->update(['is_active' => false, 'removed_at' => now()]);

                    // Create new assignment
                    Assignment::create([
                        'user_id' => $userId,
                        'project_id' => $validated['project_id'],
                        'role' => $validated['role'],
                        'assigned_at' => now()
                    ]);
                }
            });

            return back()->with('success', 'Users assigned successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign users: ' . $e->getMessage());
        }
    }
}