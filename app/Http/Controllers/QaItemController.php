<?php

namespace App\Http\Controllers;

use App\Models\Sheet;
use App\Models\QAItem;
use App\Models\QaItemReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;


class QAItemController extends Controller
{
    // ================================
    // LIST WITH FILTERS
    // ================================
    public function index(Request $request, $sheetId)
    {
        $sheet = Sheet::findOrFail($sheetId);

        $query = $sheet->qaItems()->orderBy('id', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->assigned_to) {
            $query->where('assigned_to', 'LIKE', "%{$request->assigned_to}%");
        }

        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        $sheet->qaItems = $query->get();

        return view('qa_items.index', compact('sheet'));
    }

    // ================================
    // CREATE
    // ================================
    public function create($sheetId)
    {
        $sheet = Sheet::findOrFail($sheetId);
        return view('qa_items.create', compact('sheet'));
    }

    // ================================
    // STORE
    // ================================
    public function store(Request $request, $sheetId)
    {
        $validated = $request->validate([
            'item_description' => 'required|string',
            'status' => ['required', Rule::in(QAItem::STATUSES)],
            'severity' => 'required|string|in:critical,high,medium,low',
            'comments' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|string',
        ]);

        $validated['sheet_id'] = $sheetId;

        QAItem::create($validated);

        return redirect()
            ->route('qa_items.index', $sheetId)
            ->with('success', 'QA Item created successfully!');
    }


    
    // ================================
    // EDIT
    // ================================
    public function edit($sheetId, $qaItemId)
    {
        $qaItem = QAItem::findOrFail($qaItemId);
        return view('qa_items.edit', compact('qaItem'));
    }

    // ================================
    // UPDATE
    // ================================
    public function update(Request $request, $sheetId, $qaItemId)
    {
        $request->validate([
            'item_description' => 'required|string',
            'status' => ['required', Rule::in(QAItem::STATUSES)],
            'severity' => 'required|string|in:critical,high,medium,low',
            'comments' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|string',
        ]);

        $qaItem = QAItem::findOrFail($qaItemId);
        $qaItem->update($request->all());

        return redirect()
            ->route('qa_items.index', $sheetId)
            ->with('success', 'QA Item updated successfully.');
    }

    // ================================
    // DELETE
    // ================================
    public function destroy($sheetId, $qaItemId)
    {
        QAItem::findOrFail($qaItemId)->delete();

        return redirect()
            ->route('qa_items.index', $sheetId)
            ->with('success', 'QA Item deleted successfully.');
    }

    // ================================
    // IMPORT FORM
    // ================================
    public function importForm($sheetId)
    {
        $sheet = Sheet::findOrFail($sheetId);
        return view('qa_items.import', compact('sheet'));
    }

    // ================================
    // IMPORT PREVIEW
    // ================================
    public function importPreview(Request $request, $sheetId)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        $filePath = $request->file('file')->getRealPath();
        $sheetData = IOFactory::load($filePath)->getActiveSheet()->toArray();

        $previewData = [];
        foreach ($sheetData as $index => $row) {
            if ($index === 0) continue;
            $previewData[] = [
                'description' => $row[0] ?? '',
                'status' => $row[1] ?? '',
                'due_date' => $row[2] ?? '',
                'assigned_to' => $row[3] ?? '',
            ];
        }

        $sheet = Sheet::findOrFail($sheetId);
        return view('qa_items.import_preview', compact('sheet', 'previewData'));
    }

    // ================================
    // SHOW ALL QA ITEMS
    // ================================
    public function indexAll()
    {
        $qa_items = QAItem::with('sheet.phase.project')->latest()->paginate(20);
        return view('qa_items.index_all', compact('qa_items'));
    }

    // ================================
    // ADD REVIEW
    // ================================
  public function addReview(Request $request, $sheetId, $itemId)
{
    $item = QAItem::findOrFail($itemId);

  
  $review = $item->reviews()->create([
    'user_id' => auth()->id(),
    'role'    => auth()->user()->role->name ?? 'Unknown',
    'comment' => $request->comment,
    'status'  => $request->status
]);


    // 🔥 LOG EVENT
    logActivity([
        'project_id' => $item->sheet->phase->project_id,
        'phase_id'   => $item->sheet->phase_id,
        'sheet_id'   => $item->sheet_id,
        'qa_item_id' => $item->id,

        'action_type' => 'review_added',
        'new' => [
            'comment' => $review->comment,
            'status'  => $review->status
        ],
        'note' => $review->comment
    ]);

    return back()->with('success', 'Review added.');
}


    // ================================
    // UPDATE REVIEW
    // ================================
    public function updateReview(Request $request, QaItemReview $review)
    {
        if ($review->user_id !== auth()->id()) abort(403);

        $request->validate([
            'comment' => 'required|string|max:1000',
            'status' => ['required', Rule::in(QaItemReview::REVIEW_STATUSES)],
        ]);

        $review->update($request->only('comment', 'status'));

        return back()->with('success', 'Review updated successfully.');
    }

    // ================================
    // DELETE REVIEW
    // ================================
    public function deleteReview(QaItemReview $review)
    {
        if ($review->user_id !== auth()->id()) abort(403);

        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }

    // ================================
    // QUICK STATUS CHANGE
    // ================================
    public function resolve($sheetId, $qaItemId)
    {
        QAItem::findOrFail($qaItemId)->update(['status' => 'resolved']);
        return back()->with('success', 'Item marked as resolved.');
    }

    public function verifyAll($sheetId)
    {
        Sheet::findOrFail($sheetId)
            ->qaItems()
            ->update(['status' => 'verified']);

        return back()->with('success', 'All items verified successfully!');
    }

  public function updateStatus(Request $request, $id)
{
    $item = QAItem::findOrFail($id);

    $old = $item->status;

    $item->update([
        'status' => $request->status
    ]);

    // 🔥 LOG EVENT
    logActivity([
        'project_id' => $item->sheet->phase->project_id,
        'phase_id'   => $item->sheet->phase_id,
        'sheet_id'   => $item->sheet_id,
        'qa_item_id' => $item->id,
        'action_type' => 'status_change',
        'old' => ['status' => $old],
        'new' => ['status' => $request->status],
        'note' => "Status updated to {$request->status}"
    ]);

    return back()->with('success', 'Status updated.');
}


// review list with severity filter
public function reviewsIndex(Request $request)
{
    $severity = $request->severity;
    $reviewer = $request->reviewer;
    $search   = $request->search;

    $reviews = \App\Models\QAItemReview::with(['user', 'item.sheet.phase.project'])
        ->when($severity, fn($q) =>
            $q->whereHas('item', fn($qi) => $qi->where('severity', $severity))
        )
        ->when($reviewer, fn($q) =>
            $q->where('user_id', $reviewer)
        )
        ->when($search, fn($q) =>
            $q->where('comment', 'like', "%{$search}%")
        )
        ->orderBy('created_at', 'desc')
        ->paginate(25);

    $reviewers = \App\Models\User::orderBy('name')->get();

    return view('qa_items.reviews_index', compact('reviews', 'severity', 'reviewers'));
}


}
