<?php

namespace App\Http\Controllers;

use App\Models\Sheet;
use App\Models\Phase;
use Illuminate\Http\Request;
use App\Models\QaMasterItem;
use App\Models\QaItem;

class SheetController extends Controller
{
    // عرض كل الشيتات الخاصة بفاز محددة
    public function index($phaseId)
    {
        $phase = Phase::with('sheets')->findOrFail($phaseId);
        return view('sheets.index', compact('phase'));
    }

    // صفحة إنشاء شيت جديد
    public function create($phaseId)
    {
        $phase = Phase::findOrFail($phaseId);
        return view('sheets.create', compact('phase'));
    }

    // حفظ الشيت الجديد
    public function store(Request $request, $phaseId)
    {
        $request->validate([
            'discipline' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        Sheet::create([
            'phase_id' => $phaseId,
            'discipline' => $request->discipline,
            'number' => $request->number,
            'title' => $request->title,
            'version' => $request->version,
            'status' => $request->status ?? 'Pending',
        ]);

        return redirect()->route('sheets.index', $phaseId)
            ->with('success', 'Sheet created successfully.');
    }

    // صفحة تعديل شيت
    public function edit($id)
    {
        $sheet = Sheet::findOrFail($id);
        return view('sheets.edit', compact('sheet'));
    }

    // تحديث بيانات الشيت
    public function update(Request $request, $id)
    {
        $sheet = Sheet::findOrFail($id);

        $request->validate([
            'discipline' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        $sheet->update($request->only(['discipline', 'number', 'title', 'version', 'status']));

        return redirect()->route('sheets.index', $sheet->phase_id)
            ->with('success', 'Sheet updated successfully.');
    }

    // حذف شيت
    public function destroy($id)
    {
        $sheet = Sheet::findOrFail($id);
        $phaseId = $sheet->phase_id;
        $sheet->delete();

        return redirect()->route('sheets.index', $phaseId)
            ->with('success', 'Sheet deleted successfully.');
    }

    public function indexAll()
{
    $sheets = Sheet::with('phase.project')->latest()->paginate(20);
    return view('sheets.index_all', compact('sheets'));
}


public function generateFromMaster($sheetId)
{
    $sheet = Sheet::findOrFail($sheetId);

    // لو already موجود بنود
    if ($sheet->qaItems()->count() > 0) {
        return back()->with('error', 'This sheet already has QA items. You can only generate on an empty sheet.');
    }

    $masterItems = QaMasterItem::all();

    foreach ($masterItems as $m) {
        QaItem::create([
            'sheet_id' => $sheet->id,
            'item_description' => $m->item,
            'category' => $m->category,
            'type' => $m->type,
            'notes' => $m->notes,
            'status' => 'open',
        ]);
    }

    return back()->with('success', 'Master QA items copied successfully!');
}

}
