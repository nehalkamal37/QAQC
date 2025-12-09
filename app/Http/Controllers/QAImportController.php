<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Phase;
use App\Models\Sheet;
use App\Models\QAItem;
use App\Models\ActivityLog;
use Maatwebsite\Excel\Facades\Excel;

class QAImportController extends Controller
{

    public function importPage($sheetId)
{
    $sheet = Sheet::findOrFail($sheetId);

    $projects = Project::orderBy('name')->get();
    $phases = Phase::where('project_id', $sheet->phase->project_id)->get();

    return view('qa_items.import', compact('sheet', 'projects', 'phases'));
}

    public function showImportForm()
    {
        return view('qa.import', [
            'projects' => Project::all(),
            'phases'   => Phase::all(),
            'sheets'   => Sheet::all(),
        ]);
    }

    public function preview(Request $request)
    {
        // سيتم إضافتها لاحقاً
    }

    public function confirm(Request $request)
    {
        // سيتم إضافتها لاحقاً
    }


}
