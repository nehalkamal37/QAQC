<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // عرض كل المشاريع
    public function index()
    {
        $projects = Project::all();
        return view('projects.index', compact('projects'));
    }

    // عرض صفحة إنشاء مشروع جديد
    public function create()
    {
        return view('projects.create');
    }

    // حفظ مشروع جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'client' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        Project::create($request->all());

        return redirect()->route('projects.index')->with('success', 'Project created successfully!');
    }

    // عرض تفاصيل مشروع واحد
    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }


    // عرض صفحة تعديل مشروع
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    // تحديث مشروع
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'client' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|max:255',
        ]);

        $project->update($request->all());

        return redirect()->route('projects.index')->with('success', 'Project updated successfully!');
    }

    // حذف مشروع
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully!');
    }
}
