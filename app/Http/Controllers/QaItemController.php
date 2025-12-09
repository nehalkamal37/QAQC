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


    public function index2(Request $request, $sheetId)
{
    $sheet = Sheet::findOrFail($sheetId);

    $projectId = $sheet->phase->project_id;

    // JOIN QA Items with project statuses
    $items = \App\Models\QaItem::query()
        ->where('sheet_id', $sheetId)
        ->leftJoin('project_qa_item_statuses as p', function($join) use ($projectId) {
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
            'p.assigned_to as project_assigned_to'
        )
        ->get();

        
        if ($request->assigned_to) {
            $query->where('assigned_to', 'LIKE', "%{$request->assigned_to}%");
        }

    return view('qa_items.index', compact('sheet', 'items', 'projectId'));
}

    public function index1(Request $request, $sheetId)
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
  public function store(Request $request, $sheetId)
{
    $validated = $request->validate([
        'item_description' => 'required|string',
        'status' => ['required', Rule::in(QAItem::STATUSES)],
        'severity' => 'required|string|in:critical,high,medium,low',
        'comments' => 'nullable|string',
        'due_date' => 'nullable|date',
        'assigned_to' => 'nullable|string',

        // DO NOT insert these into qa_items, only into project_qa_item_statuses
        'category' => 'nullable|string',
        'title'    => 'nullable|string',
    ]);

    // Remove category + title from the array so QAItem::create() doesn't try to insert them
    $qaItemData = Arr::except($validated, ['category', 'title']);

    // Add sheet ID
    $qaItemData['sheet_id'] = $sheetId;

    // Create QA Item Template
    $qaItem = QAItem::create($qaItemData);

    // Create project-specific status
    ProjectQAItemStatus::create([
        'project_id'  => Sheet::findOrFail($sheetId)->phase->project_id,
        'qa_item_id'  => $qaItem->id,
        'category'    => $request->category,
        'title'       => $request->title,
        'applicable'   => $request->applicable,
'incorporated' => $request->incorporated,
'confirmed'    => $request->confirmed,

    ]);

    return redirect()
        ->route('qa_items.index', $sheetId)
        ->with('success', 'QA Item created successfully!');
}



    
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
public function update(Request $request, $sheetId, $id)
{
    $qaItem = QaItem::findOrFail($id);
    $projectId = $qaItem->sheet->phase->project_id;

    // ----------- 1) Get old values BEFORE update -------

    // هات الـ status القديم لنفس المشروع + نفس الـ QA item
    $existingStatus = ProjectQAItemStatus::where('project_id', $projectId)
        ->where('qa_item_id', $qaItem->id)
        ->first();

    $oldDue      = optional($existingStatus)->due_date;
    $oldAssigned = optional($existingStatus)->assigned_to;

    // ----------- 2) Update QA Item Template ----------
    $qaItem->update([
        'item_description' => $request->item_description,
        'severity'         => $request->severity,
    ]);

    // ----------- 3) Update Project Status ------------
    $projectStatus = ProjectQAItemStatus::firstOrCreate([
        'project_id' => $projectId,
        'qa_item_id' => $qaItem->id,
    ]);

    $projectStatus->update([

        'applicable'   => $request->applicable,
        'incorporated' => $request->incorporated,
        'confirmed'    => $request->confirmed,
        'comments'     => $request->project_comments,
        'due_date'     => $request->project_due_date ?? null,
        'assigned_to'  => $request->project_assigned_to ?? null,
        'category'     => $request->category,
        'title'        => $request->title,
    ]);

    // ---------- 4) Trigger Notifications -----------

    // A) Assigned_to Changed → Send Assignment Notification
    if ($oldAssigned != $request->project_assigned_to && $request->project_assigned_to) {
        NotificationService::notifyQaItemAssigned(
            $request->project_assigned_to,
            $qaItem->id,
            $qaItem->sheet->phase->project->name
        );
    }

    // B) Due Date change → send reminder
    if ($oldDue != $request->project_due_date && $request->project_due_date &&
     $request->assigned_to != null    ) {

        $dueDate = \Carbon\Carbon::parse($request->project_due_date);
        $today   = now();

        $daysUntilDue = $today->diffInDays($dueDate, false);

        NotificationService::notifyDueDateReminder(
            $request->project_assigned_to,
            $qaItem->id,
            $daysUntilDue
        );
    }

    // .. باقي الريدايركت / الفلاش ميسيدج


    return redirect()->route('qa_items.index', $sheetId)
        ->with('success', 'QA Item Updated Successfully');
}



  public function update1(Request $request, $sheetId, $qaItemId)
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
    $oldStatus = $qaItem->status;
    $oldDueDate = $qaItem->due_date;
    
    // Update the QA item
    $qaItem->update($request->all());

    // DEBUG: Log what's happening
    \Log::info('QA Item Update', [
        'item_id' => $qaItem->id,
        'old_status' => $oldStatus,
        'new_status' => $qaItem->status,
        'assigned_to' => $qaItem->assigned_to,
        'status_changed' => $oldStatus !== $qaItem->status
    ]);

    // Send notification if status changed and assigned to someone
    if ($oldStatus !== $qaItem->status && $qaItem->assigned_to) {
        \Log::info('Sending status change notification');
        \App\Services\NotificationService::notifyStatusChange(
            $qaItem->assigned_to,
            $qaItem->id,
            $oldStatus,
            $qaItem->status
        );
    }

  
    // In your QaItemController update method - fix the due date calculation
if ($oldDueDate != $qaItem->due_date && $qaItem->due_date) {
    // Use floor() to get whole days and handle timezone issues
    $daysUntilDue = now()->startOfDay()->diffInDays($qaItem->due_date->startOfDay(), false);
    
    if ($qaItem->assigned_to) {
        \Log::info('Sending due date notification', ['days_until_due' => $daysUntilDue]);
        \App\Services\NotificationService::notifyDueDateReminder(
            $qaItem->assigned_to,
            $qaItem->id,
            $daysUntilDue
        );
    }
}
    return redirect()
        ->route('qa_items.index', $sheetId)
        ->with('success', 'QA Item updated successfully.');
}

    // ================================
    // DELETE
    // ================================
    public function destroy1($sheetId, $qaItemId)
    {
        QAItem::findOrFail($qaItemId)->delete();


        return redirect()
            ->route('qa_items.index', $sheetId)
            ->with('success', 'QA Item deleted successfully.');
    }

    public function destroy($sheetId, $qaItemId)
{
    // Ensure QA Item exists
    $qaItem = QAItem::findOrFail($qaItemId);

    // Delete all project-specific statuses for this QA item
    ProjectQAItemStatus::where('qa_item_id', $qaItemId)->delete();

    // Delete the QA item template
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
   /* public function importPreview(Request $request, $sheetId)
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
*/

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
   
    QaItem::create([
    'sheet_id' => $sheetId,
    'item_description' =>
        ($item['topic'] ?? '') . ' - ' .
        ($item['category'] ?? '') . ' - ' .
        ($item['item'] ?? ''),

    'comments' => $item['notes'] ?? null,
    'status' => $item['status'] ?? 'open',
    'due_date' => $item['due_date'] ?? null,
    'assigned_to' => $item['assigned_to'] ?? null,
    'severity' => 'medium',
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




// pdf upload 
/*
public function importPdfPreview(Request $request, $sheetId)
{
    $request->validate([
        'file' => 'required|mimes:pdf'
    ]);

    $parser = new Parser();
    $pdf = $parser->parseFile($request->file('file')->getRealPath());
    $text = $pdf->getText();

    $lines = preg_split("/\r\n|\n|\r/", $text);

    $previewData = [];
    $currentSection = '';

    foreach ($lines as $line) {

        // Section detection (ALL CAPS LINE)
        if (preg_match('/^[A-Z ]+$/', trim($line)) && strlen(trim($line)) > 5) {
            $currentSection = trim($line);
            continue;
        }

        // Normal checklist item (starts with checkbox icon)
        if (preg_match('/^☐/', trim($line))) {
            // Remove checkbox symbol
            $clean = trim(str_replace('☐', '', $line));

            $previewData[] = [
                'section' => $currentSection,
                'item' => $clean,
                'status' => 'open',
                'notes' => '',
                'due_date' => '',
                'assigned_to' => ''
            ];
        }
    }

    $sheet = Sheet::findOrFail($sheetId);

    return view('qa_items.pdf_preview', compact('sheet', 'previewData'));
}
*/
/*
public function importPdfPreview(Request $request, $sheetId)
{
    $request->validate([
        'file' => 'required|mimes:pdf'
    ]);

    $pdfPath = $request->file('file')->getRealPath();
    $outputBase = storage_path('app/pdf_images/page');

    // 1) PDF → PNG
    $cmd = "\"C:\\poppler\\bin\\pdftoppm.exe\" \"$pdfPath\" \"$outputBase\" -png";
    exec($cmd);

    // Collect Images
    $images = glob(storage_path('app/pdf_images/page*.png'));

    $previewData = [];

    foreach ($images as $image) {

        // 2) OCR extract text from PNG
        $textFile = $image . '.txt';
        $ocrCmd = "\"C:\\Program Files\\Tesseract-OCR\\tesseract.exe\" \"$image\" \"$textFile\" -l eng";
        exec($ocrCmd);

        // Tesseract outputs: page-1.png.txt
        $text = file_get_contents($textFile . '.txt');

        $lines = preg_split("/\r\n|\n|\r/", $text);

        foreach ($lines as $line) {

            // normalize: convert 'x', 'X', '✓' to a checked symbol
            $normalized = str_replace(['x', 'X', '✓'], '☒', $line);

            if (preg_match('/^(☐|☒)\s+(☐|☒)\s+(☐|☒)\s+(.*)$/u', $normalized, $m)) {

                $box1 = $m[1];
                $box2 = $m[2];
                $box3 = $m[3];
                $item = trim($m[4]);

                if (in_array('☒', [$box1, $box2, $box3])) {

                    $previewData[] = [
                        'section' => 'Auto-Detected',
                        'item' => $item,
                        'status' => 'done'
                    ];
                }
            }
        }
    }
$sheet = Sheet::findOrFail($sheetId);
return view('qa_items.pdf_preview', compact('sheet', 'previewData'));

}


public function importPdfForm($sheetId)
{
    $sheet = Sheet::findOrFail($sheetId);
    return view('qa_items.import_pdf_form', compact('sheet'));
}


public function importPdfConfirm(Request $request, $sheetId)
{
    
    if (!$request->has('items') || !is_array($request->items)) {
        return back()->with('error', 'No items were received from PDF preview. Please retry the import.');
    }

    foreach ($request->items as $item) {

        // Skipping empty rows
        if (empty($item['item']) && empty($item['section'])) {
            continue;
        }

        QaItem::create([
            'sheet_id' => $sheetId,
            'item_description' => $item['item'] ?? 'Untitled Item',
            'comments' => $item['section'] ?? '',
            'status' => $item['status'] ?? 'open',
            'due_date' => null,
            'assigned_to' => null,
            'severity' => 'medium',
        ]);
    }

    return redirect()
        ->route('qa_items.index', $sheetId)
        ->with('success', 'PDF QA Items imported successfully.');
}

*/


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

        foreach ($request->items as $item) {

            if (empty($item['item']) && empty($item['section'])) {
                continue;
            }

            QaItem::create([
                'sheet_id'         => $sheetId,
                'item_description' => $item['item'] ?? 'Untitled Item',
                'comments'         => $item['section'] ?? '',
                'status'           => $item['status'] ?? 'open',
                'due_date'         => null,
                'assigned_to'      => null,
                'severity'         => 'medium',
            ]);
        }

        return redirect()
            ->route('qa_items.index', $sheetId)
            ->with('success', 'PDF items imported successfully.');
    }


    public function assignToMe(QaItem $qa_item)
{
    $qa_item->update(['assigned_to' => auth()->id()]);
    return back()->with('success', 'QA item assigned to you!');
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


}
