<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;  
use App\Models\Role;

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
        $pmRoleId = Role::where('name', 'PM')->value('id');

        $pm = User::where('role_id', $pmRoleId)->get();


        return view('projects.create', compact('pm'));
    }

    // حفظ مشروع جديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'client' => 'nullable|string|max:255',
            'pm_id' => 'nullable|exists:users,id',
            'status' => 'nullable|string|max:255',
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

        $pmRoleId = Role::where('name', 'PM')->value('id');

        $pm = User::where('role_id', $pmRoleId)->get();


        return view('projects.edit', compact('project', 'pm'));
    }

    // تحديث مشروع
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'client' => 'nullable|string|max:255',
            'pm_id' => 'nullable|exists:users,id',
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
