<?php

namespace App\Http\Controllers;

use App\Models\Phase;
use App\Models\Project;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    public function index($projectId)
    {
        $project = Project::with('phases')->findOrFail($projectId);
        return view('phases.index', compact('project'));
    }

    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        return view('phases.create', compact('project'));
    }

    public function store(Request $request, $projectId)
    {
        $request->validate([
            'type' => 'required|string',
            'due_date' => 'nullable|date',
            'status' => 'required|string',
        ]);

        Phase::create([
            'project_id' => $projectId,
            'type' => $request->type,
            'due_date' => $request->due_date,
            'status' => $request->status,
        ]);

        return redirect()->route('phases.index', $projectId)->with('success', 'Phase added successfully!');
    }
 public function show($id)
{
    $phase = Phase::with([
        'project',
        'sheets',
        'assignedUser'
    ])->findOrFail($id);

    return view('phases.show', compact('phase'));
}

    public function edit($id)
    {
        $phase = Phase::findOrFail($id);
        return view('phases.edit', compact('phase'));
    }

    public function update(Request $request, $id)
    {
        $phase = Phase::findOrFail($id);

        $phase->update($request->validate([
            'type' => 'required|string',
            'due_date' => 'nullable|date',
            'status' => 'required|string',
        ]));

        return redirect()->route('phases.index', $phase->project_id)->with('success', 'Phase updated successfully!');
    }

    public function destroy($id)
    {
        $phase = Phase::findOrFail($id);
        $projectId = $phase->project_id;
        $phase->delete();

        return redirect()->route('phases.index', $projectId)->with('success', 'Phase deleted successfully!');
    }

    public function indexAll()
{
    $phases = Phase::with('project')->latest()->paginate(20);
    return view('phases.index_all', compact('phases'));
}

}
