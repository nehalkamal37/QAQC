<?php

namespace App\Http\Controllers;

use App\Models\Sheet;
use App\Models\QAItem;
use App\Models\QaItemReview;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use App\Services\NotificationService;
use App\Models\ProjectQAItemStatus;
use App\Models\User;
use Illuminate\Support\Arr;

class QAItemController extends Controller
{
    // ================================
    // LIST WITH FILTERS
    // ================================

    public function index(Request $request, $sheetId)
{
    $sheet = Sheet::findOrFail($sheetId);

    $projectId = $sheet->phase->project_id;

    // Build query (DO NOT call ->get() yet)
    $query = \App\Models\QaItem::query()
        ->where('qa_items.sheet_id', $sheetId)
        ->leftJoin('project_qa_item_statuses as p', function ($join) use ($projectId) {
            $join->on('p.qa_item_id', '=', 'qa_items.id')
                ->where('p.project_id', '=', $projectId);
        })
        ->select(
            'qa_items.*',
            'p.applicable',
            'p.incorporated',
            'p.confirmed',
            'p.comments as project_comments',
            'p.due_date as project_due_date',
            'p.assigned_to as project_assigned_to',
            'p.category',
            'p.title'
        );

    // Apply filters
    if ($request->assigned_to) {
        $query->where('p.assigned_to', 'LIKE', "%{$request->assigned_to}%");
    }

    if ($request->status) {
        $query->where('qa_items.status', $request->status);
    }

    if ($request->severity) {
        $query->where('qa_items.severity', $request->severity);
    }

    // Load results AFTER filters
    $items = $query->paginate(15); // Or any number you prefer
    

    return view('qa_items.index', compact('sheet', 'items', 'projectId'));
}


   

    // ================================
    // CREATE
    // ================================
    public function create($sheetId)
    {
        $sheet = Sheet::findOrFail($sheetId);
        $project = $sheet->phase->project;
       // $project = $project->getEngineers();
        return view('qa_items.create', compact('sheet', 'project'));
    }


    public function show(QaItem $qa_item)
    {
        // Eager load relationships to avoid N+1 queries
        $qa_item->load(['sheet.phase.project', 'assignedUser', 'reviews.user']);
        $phase = $qa_item->sheet->phase;
        $project = $phase->project;


        return view('qa_items.show', compact('qa_item', 'phase', 'project'));
    }



    // ================================
    // STORE
    // ================================
  


    
    // ================================
    // EDIT
    // ================================
public function edit($sheetId, $id)
{
    $qaItem = QaItem::findOrFail($id);

    // Get project ID via sheet → phase → project
    $projectId = $qaItem->sheet->phase->project_id;

    // Fetch the project's specific status
    $projectStatus = ProjectQAItemStatus::where('project_id', $projectId)
        ->where('qa_item_id', $qaItem->id)
        ->first();

    // Load all users for the dropdown
    $users = User::all();

    return view('qa_items.edit', compact('qaItem', 'projectStatus', 'users'));
}


    // ================================
    // UPDATE
    // ================================

 




    // ================================
    // DELETE
    // ================================
  

  public function destroy($sheetId, $qaItemId)
{
    $qaItem = QAItem::findOrFail($qaItemId);

    // 🔥 Logging قبل الحذف
    logActivity([
        'project_id'  => $qaItem->sheet->phase->project_id,
        'phase_id'    => $qaItem->sheet->phase_id,
        'sheet_id'    => $qaItem->sheet_id,
        'qa_item_id'  => $qaItem->id,
        'action_type' => 'item_deleted',
        'old'         => [
            'item_description' => $qaItem->item_description,
            'severity'         => $qaItem->severity,
            'status'           => $qaItem->status,
            'assigned_to'      => $qaItem->assigned_to,
        ],
        'new'         => null,
        'note'        => "QA Item deleted"
    ]);

    ProjectQAItemStatus::where('qa_item_id', $qaItemId)->delete();
    $qaItem->delete();

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
            'topic'        => $row[0] ?? '',
            'category'     => $row[1] ?? '',
            'item'         => $row[2] ?? '',
            'notes'        => $row[3] ?? '',
            'status'       => $row[4] ?? 'open',
            'due_date'     => $row[5] ?? null,
            'assigned_to'  => $row[6] ?? null,
        ];
    }

    // Store in session instead of hidden inputs
    session(['import_preview_'.$sheetId => $previewData]);

    $sheet = Sheet::findOrFail($sheetId);

    return view('qa_items.import_preview', compact('sheet', 'previewData'));
}

public function importConfirm(Request $request, $sheetId)
{
    $sheet = Sheet::findOrFail($sheetId);

    foreach ($request->items as $item) {

        $qa = QaItem::create([
            'sheet_id'         => $sheetId,
            'item_description' => ($item['topic'] ?? '') . ' - ' . ($item['category'] ?? '') . ' - ' . ($item['item'] ?? ''),
            'comments'         => $item['notes'] ?? null,
            'status'           => $item['status'] ?? 'open',
            'due_date'         => $item['due_date'] ?? null,
            'assigned_to'      => $item['assigned_to'] ?? null,
            'severity'         => 'medium',
        ]);

        // 🔥 Logging
        logActivity([
            'project_id'  => $sheet->phase->project_id,
            'phase_id'    => $sheet->phase_id,
            'sheet_id'    => $sheetId,
            'qa_item_id'  => $qa->id,
            'action_type' => 'item_imported',
            'old'         => null,
            'new'         => $qa->toArray(),
            'note'        => "Item imported from Excel"
        ]);
    }

    return redirect()
        ->route('qa_items.index', $sheetId)
        ->with('success', 'QA items imported successfully.');
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

   

    // ================================
    // UPDATE REVIEW
    // ================================
   public function updateReview(Request $request, QaItemReview $review)
{
    if ($review->user_id !== auth()->id()) abort(403);

    $request->validate([
        'comment' => 'required|string|max:1000',
        'status'  => ['required', Rule::in(QaItemReview::REVIEW_STATUSES)],
    ]);

    $old = [
        'comment' => $review->comment,
        'status'  => $review->status,
    ];

    $review->update($request->only('comment', 'status'));

    $new = [
        'comment' => $review->comment,
        'status'  => $review->status,
    ];

    // 🔥 Logging
    logActivity([
        'project_id'  => $review->item->sheet->phase->project_id,
        'phase_id'    => $review->item->sheet->phase_id,
        'sheet_id'    => $review->item->sheet_id,
        'qa_item_id'  => $review->qa_item_id,
        'action_type' => 'review_updated',
        'old'         => $old,
        'new'         => $new,
        'note'        => "Review updated by {$review->user->name}"
    ]);

    return back()->with('success', 'Review updated successfully.');
}

    // ================================
    // DELETE REVIEW
    // ================================
   
   
    // ================================
    // QUICK STATUS CHANGE
    // ================================
    public function resolve($sheetId, $qaItemId)
    {
        QAItem::findOrFail($qaItemId)->update(['status' => 'resolved']);
        return back()->with('success', 'Item marked as resolved.');
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

public function preview(Request $request)
{
    $request->validate([
        'project_id' => 'required|integer',
        'phase_id'   => 'required|integer',
        'sheet_id'   => 'required|integer',
        'file'       => 'required|file|mimes:xlsx,xls'
    ]);

    // Read the Excel File
    $path = $request->file('file')->getRealPath();
    $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $path)[0]; // Only first sheet

    // Remove empty rows
    $rows = array_filter($data, function($r) {
        return array_filter($r);
    });

    if (count($rows) < 2) {
        return back()->with('error', 'The file is empty or has no valid rows.');
    }

    // Assume first row = headers
    $headers = $rows[0];
    unset($rows[0]);

    // Package all preview data
    $previewData = [
        'project_id' => $request->project_id,
        'phase_id'   => $request->phase_id,
        'sheet_id'   => $request->sheet_id,
        'headers'    => $headers,
        'rows'       => $rows,
    ];

    return view('qa.import_preview', $previewData);
}





    /* ============================================================
       1) PREVIEW PDF → detect rows with checked checkboxes
    ============================================================ */
public function importPdfPreview(Request $request, $sheetId)
{
    $request->validate([
        'file' => 'required|mimes:pdf'
    ]);

    $sheet = Sheet::findOrFail($sheetId);

    // Save uploaded file
    $pdfPath = $request->file('file')->getRealPath();

    // Convert PDF → PNG images
    $outputDir = storage_path("app/pdf_pages");
    if (!file_exists($outputDir)) mkdir($outputDir, 0777, true);

    $pdftoppm = "C:\\poppler\\Library\\bin\\pdftoppm.exe"; // Windows
    // $pdftoppm = "/usr/bin/pdftoppm"; // Linux

    $outputBase = $outputDir . "/page";

    $cmd = "\"$pdftoppm\" -png \"$pdfPath\" \"$outputBase\"";
    exec($cmd);

    $images = glob("$outputDir/page*.png");

    $python = "C:\\Users\\Alforsan Store\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
    $script = "C:\\qaqc-tool\\python\\detect_checks.py";

    $previewData = [];
    $rowIndex = 1;

    foreach ($images as $img) {
        $command = "\"$python\" \"$script\" \"$img\"";
        exec($command, $out);

        $json = json_decode(implode("", $out), true);

        if (!$json || !isset($json["checkboxes"]))
            continue;

        foreach ($json["checkboxes"] as $cb) {

            if ($cb["checked"]) {
                $previewData[] = [
                    "section" => "Auto",
                    "item"    => "Row " . $rowIndex,
                    "status"  => "done"
                ];
            }

            $rowIndex++;
        }
    }

    return view('qa_items.pdf_preview', compact('sheet', 'previewData'));
}



public function importPdfForm($sheetId)
{
    $sheet = Sheet::findOrFail($sheetId);
    return view('qa_items.import_pdf_form', compact('sheet'));
}

    /* ============================================================
       2) IMPORT CONFIRMATION → Save to DB
    ============================================================ */
   public function importPdfConfirm(Request $request, $sheetId)
{
    if (!$request->has('items') || !is_array($request->items)) {
        return back()->with('error', 'No items received from PDF.');
    }

    $sheet = Sheet::findOrFail($sheetId);

    foreach ($request->items as $item) {

        $qa = QaItem::create([
            'sheet_id'         => $sheetId,
            'item_description' => $item['item'] ?? 'Untitled Item',
            'comments'         => $item['section'] ?? '',
            'status'           => $item['status'] ?? 'open',
            'due_date'         => null,
            'assigned_to'      => null,
            'severity'         => 'medium',
        ]);

        // 🔥 Logging
        logActivity([
            'project_id'  => $sheet->phase->project_id,
            'phase_id'    => $sheet->phase_id,
            'sheet_id'    => $sheetId,
            'qa_item_id'  => $qa->id,
            'action_type' => 'item_imported',
            'old'         => null,
            'new'         => $qa->toArray(),
            'note'        => "Item imported from PDF"
        ]);
    }

    return redirect()
        ->route('qa_items.index', $sheetId)
        ->with('success', 'PDF items imported successfully.');
}




public function updateStatusAssigned(Request $request, QaItem $qa_item)
{
    $validated = $request->validate([
        'status' => 'required|in:open,in_progress,needs_info,resolved,verified,closed'
    ]);
    
    $qa_item->update(['status' => $validated['status']]);
    return back()->with('success', 'Status updated successfully!');
}



// If you have separate methods for quick actions
public function markInProgress(QaItem $qaItem)
{
    $oldStatus = $qaItem->status;
    $qaItem->update(['status' => 'in_progress']);

    // ADD THIS: Send notification
    if ($qaItem->assigned_to) {
        NotificationService::notifyStatusChange(
            $qaItem->assigned_to,
            $qaItem->id,
            $oldStatus,
            'in_progress'
        );
    }

    return back()->with('success', 'QA item marked as in progress');
}



 // updated version with roles considerations


    /* ===========================================================
        ROLE & STATUS RULE ENGINE
    =========================================================== */

    private function canTransition($user, $from, $to, QAItem $item)
    {
        // Admin can do anything
        if ($user->hasRole('Admin')) return true;

        // Prevent CLOSED → anything
        if ($from === 'closed') return false;

        // Valid chains
        $valid = [
            'open'         => ['in_progress'],
            'in_progress'  => ['needs_info', 'resolved'],
            'needs_info'   => ['in_progress', 'resolved'],
            'resolved'     => ['verified'],
            'verified'     => ['closed'],
        ];

        // Not a valid step at all
        if (!in_array($to, $valid[$from] ?? [])) return false;

        /* ----------------------------------------
            Role-based restrictions
        ---------------------------------------- */

        if ($to === 'in_progress') {
            return $user->hasRole(['Engineer','Night Vision']) ||
                   $user->hasRole(['PM','Senior Reviewer']);
        }

        if ($to === 'resolved') {
            return $user->hasRole(['Engineer','Night Vision']) &&
                   $item->assigned_to == $user->name;
        }

        if ($to === 'verified') {
            return $user->hasRole(['PM','Senior Reviewer','Admin']);
        }

        if ($to === 'closed') {
            return $user->hasRole('PM');
        }

        return false;
    }

    /* ===========================================================
        UPDATE STATUS (Single Endpoint)
    =========================================================== */
    public function updateStatus1(Request $request, $id)
    {
        $item = QAItem::findOrFail($id);
        $user = auth()->user();

        $request->validate([
            'status' => 'required|in:open,in_progress,needs_info,resolved,verified,closed'
        ]);

        $new = $request->status;
        $old = $item->status;

        // Enforce rule engine
        if (!$this->canTransition($user, $old, $new, $item)) {
            return back()->with('error', "You are not allowed to change status $old → $new");
        }

        $item->update(['status' => $new]);

        return back()->with('success', "Status changed from $old to $new");
    }


public function update(Request $request, $sheetId, $id)
{
    $item = QAItem::findOrFail($id);

    $old = [
        'item_description' => $item->item_description,
        'severity'         => $item->severity,
        'assigned_to'      => $item->assigned_to,
    ];

 $oldAssigned = $item->assigned_to;

$item->update([
    'item_description' => $request->item_description,
    'severity'         => $request->severity,
    'assigned_to'      => $request->assigned_to ?: $item->assigned_to,
]);

$newAssigned = $item->assigned_to;

// Log
logActivity([
    'project_id'  => $item->sheet->phase->project_id,
    'phase_id'    => $item->sheet->phase_id,
    'sheet_id'    => $item->sheet_id,
    'qa_item_id'  => $item->id,
    'action_type' => 'qa_assignment',
    'old'         => ['assigned_to' => $oldAssigned],
    'new'         => ['assigned_to' => $newAssigned],
    'note'        => "Assigned changed to {$newAssigned}",
]);

// Notify
NotificationService::notifyQaItemAssigned(
    $newAssigned,
    $item->id,
    $item->sheet->phase->project->name
);




    return redirect()->route('qa_items.index',$sheetId)
        ->with('success','QA Item updated.');
}


    public function updateStatus(Request $request, $id)
{
    $item = QAItem::findOrFail($id);
    $user = auth()->user();

    $request->validate([
        'status' => 'required|in:open,in_progress,needs_info,resolved,verified,closed'
    ]);

    $new = $request->status;
    $old = $item->status;

    if (!$this->canTransition($user, $old, $new, $item)) {
        return back()->with('error', "You are not allowed to change status $old → $new");
    }

    $item->update(['status' => $new]);

    // ADD THIS 🔥
    logActivity([
        'project_id' => $item->sheet->phase->project_id,
        'phase_id'   => $item->sheet->phase_id,
        'sheet_id'   => $item->sheet_id,
        'qa_item_id' => $item->id,
        'action_type' => 'status_change',
        'old' => ['status' => $old],
        'new' => ['status' => $new],
        'note' => "Status changed from $old to $new"
    ]);

    return back()->with('success', "Status changed from $old to $new");
}

    /* ===========================================================
        VERIFY ALL (Safe Version)
    =========================================================== */
    public function verifyAll($sheetId)
    {
        $user = auth()->user();

        if (!$user->hasRole(['Admin','PM','Senior Reviewer'])) {
            return back()->with('error','You cannot verify all.');
        }

        $sheet = Sheet::findOrFail($sheetId);

        $items = $sheet->qaItems;

        // SR can verify all only if everything is resolved
        if ($user->hasRole('Senior Reviewer')) {
            if ($items->contains(fn($i) => $i->status !== 'resolved')) {
                return back()->with('error', 'Only PM/Admin can bulk verify if not all items are resolved.');
            }
        }

        foreach ($items as $item) {
            if ($this->canTransition($user, $item->status, 'verified', $item)) {
                $item->update(['status' => 'verified']);
            }
        }

        return back()->with('success', 'All eligible items verified!');
    }

    /* ===========================================================
        STORE
    =========================================================== */
    public function store(Request $request, $sheetId)
    {
        $validated = $request->validate([
            'item_description' => 'required|string',
            'severity' => 'required|in:critical,high,medium,low',
            'comments' => 'nullable|string',
        ]);

        $validated['sheet_id'] = $sheetId;
        $validated['status'] = 'open';

        QAItem::create($validated);

        return redirect()->route('qa_items.index',$sheetId)
            ->with('success','QA Item created.');
    }

    /* ===========================================================
        UPDATE (NO STATUS CHANGES HERE)
    =========================================================== */
 

    /* ===========================================================
        ADD REVIEW
    =========================================================== */
   public function addReview(Request $request, $sheetId, $itemId)
{
    $request->validate([
        'comment' => 'required|string',
        'status'  => 'required'
    ]);

    $user = auth()->user();

    $allowed = $user->hasRole('PM')
        ? ['noted','open','resolved','verified','closed']
        : ['noted','open','needs_info','verified'];

    if (!in_array($request->status, $allowed)) {
        return back()->with('error','Not allowed review status.');
    }

    $review = QaItemReview::create([
        'qa_item_id' => $itemId,
        'user_id'    => $user->id,
        'role'       => $user->getRoleNames()->first(),
        'comment'    => $request->comment,
        'status'     => $request->status,
    ]);

    // 🔥 Logging
    logActivity([
        'project_id'  => $review->item->sheet->phase->project_id,
        'phase_id'    => $review->item->sheet->phase_id,
        'sheet_id'    => $review->item->sheet_id,
        'qa_item_id'  => $itemId,
        'action_type' => 'review_added',
        'old'         => null,
        'new'         => [
            'comment' => $request->comment,
            'status'  => $request->status
        ],
        'note'        => "Review added by {$user->name}"
    ]);

    return back()->with('success','Review added.');
}


    /* ===========================================================
        DELETE REVIEW (PM or Owner Only)
    =========================================================== */
  public function deleteReview(QaItemReview $review)
{
    $user = auth()->user();

    if ($review->user_id !== $user->id && !$user->hasRole('PM')) {
        abort(403);
    }

    $old = [
        'comment' => $review->comment,
        'status'  => $review->status,
    ];

    $item = $review->item;

    $review->delete();

    // 🔥 Logging
    logActivity([
        'project_id'  => $item->sheet->phase->project_id,
        'phase_id'    => $item->sheet->phase_id,
        'sheet_id'    => $item->sheet_id,
        'qa_item_id'  => $item->id,
        'action_type' => 'review_deleted',
        'old'         => $old,
        'new'         => null,
        'note'        => "Review deleted by {$user->name}"
    ]);

    return back()->with('success','Review deleted.');
}


    /* ===========================================================
        ASSIGN TO ME
    =========================================================== */
  public function assignToMe(QAItem $item)
{
    $user = auth()->user();

    if (!$user->hasRole(['Engineer','Night Vision'])) {
        return back()->with('error','Only engineers can self-assign');
    }

    $oldAssigned = $item->assigned_to;

    $item->update(['assigned_to' => $user->name]);

    // 🔥 Logging
    logActivity([
        'project_id'  => $item->sheet->phase->project_id,
        'phase_id'    => $item->sheet->phase_id,
        'sheet_id'    => $item->sheet_id,
        'qa_item_id'  => $item->id,
        'action_type' => 'qa_assignment',
        'old'         => ['assigned_to' => $oldAssigned],
        'new'         => ['assigned_to' => $user->name],
        'note'        => "{$user->name} assigned himself to the item"
    ]);

NotificationService::notifyQaItemAssigned(
    $newAssigned,
    $item->id,
    $item->sheet->phase->project->name
);


    return back()->with('success','Assigned to you!');
}



}
