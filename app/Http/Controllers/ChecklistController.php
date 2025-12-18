<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use App\Models\QAItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\Project;
use App\Models\Phase;
use App\Models\Sheet;
use App\Models\ProjectQAItemStatus;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ChecklistController extends Controller
{


    public function showForm()
{
    $projects = Project::with(['phases.sheets'])->get();

    // نبني JSON جاهز للـ JS بدون Closures
    $projectsJson = $projects->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'phases' => $p->phases->map(function($ph) {
                return [
                    'id' => $ph->id,
                    'type' => $ph->type,
                    'sheets' => $ph->sheets->map(function($s) {
                        return [
                            'id' => $s->id,
                            'number' => $s->number,
                            'title' => $s->title,
                        ];
                    }),
                ];
            }),
        ];
    });

    return view('checklist.upload', [
        'projects' => $projects,
        'projectsJson' => $projectsJson
    ]);
}


    public function showForm2()
{
    $projects = Project::with(['phases.sheets'])->get();

    // نبني JSON جاهز للـ JS بدون Closures
    $projectsJson = $projects->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'phases' => $p->phases->map(function($ph) {
                return [
                    'id' => $ph->id,
                    'type' => $ph->type,
                    'sheets' => $ph->sheets->map(function($s) {
                        return [
                            'id' => $s->id,
                            'number' => $s->number,
                            'title' => $s->title,
                        ];
                    }),
                ];
            }),
        ];
    });

    return view('checklist.upload_excel', [
        'projects' => $projects,
        'projectsJson' => $projectsJson
    ]);
}
    public function uploadAndSave2(Request $request)
    {
        set_time_limit(120);

        $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'phase_id'      => 'required|exists:phases,id',
            'sheet_id'      => 'required|exists:sheets,id',
            'checklist_pdf' => 'required|file|mimes:pdf',
        ]);

        try {
            // Upload PDF
            $path = $request->file('checklist_pdf')->store('pdf_uploads');
            $fullPath = Storage::path($path);

            // Run Python script
            $scriptPath = base_path('python/extract_pdf_fields.py');

            $process = new Process([
                'python3',
                $scriptPath,
                $fullPath
            ]);

            $process->setTimeout(120);
            $process->run();

            Storage::delete($path);

            if (!$process->isSuccessful()) {
                Log::error('Python process failed: ' . $process->getErrorOutput());
                return back()->with('error', 'PDF processing failed.');
            }

            $output = json_decode($process->getOutput(), true);

            if (!$output || isset($output['error'])) {
                Log::error('Python output error: ' . json_encode($output));
                return back()->with('error', 'Could not parse PDF.');
            }

            $savedCount = 0;

            foreach ($output as $description => $values) {
                if (!$description || strlen(trim($description)) < 3) {
                    continue;
                }

                $checkboxJson = [
                    "applicable"   => $values["applicable"] ?? false,
                    "incorporated" => $values["incorporated"] ?? false,
                    "confirmed"    => $values["confirmed"] ?? false,
                ];

                // لو حابب تستخدم الـ 3 checkboxes كـ logic للـ status
                $status = 'open';
                if ($checkboxJson['applicable'] && !$checkboxJson['incorporated']) {
                    $status = 'pending';
                }
                if ($checkboxJson['applicable'] && $checkboxJson['incorporated'] && !$checkboxJson['confirmed']) {
                    $status = 'resolved'; // مثلاً
                }
                if ($checkboxJson['applicable'] && $checkboxJson['incorporated'] && $checkboxJson['confirmed']) {
                    $status = 'closed';
                }

                QaItem::create([
                    'sheet_id'         => $request->sheet_id,  // ✅ مش ثابت 2
                    'item_description' => trim($description),
                    'checkbox_values'  => $checkboxJson,
                    'status'           => $status,
                    'comments'         => null,
                    'due_date'         => null,
                    'assigned_to'      => null,
                    'severity'         => 'medium',
                ]);

                $savedCount++;
            }

            if ($savedCount > 0) {
                return back()->with('success', "{$savedCount} items imported successfully!");
            }

            return back()->with('error', 'No items imported.');

        } catch (\Exception $e) {
            Log::error('Checklist upload error: ' . $e->getMessage());
            return back()->with('error', "Error: " . $e->getMessage());
        }
    }



public function uploadAndSave(Request $request)
{
    set_time_limit(300); // Enough time, but Python will finish fast

    $request->validate([
        'checklist_pdf' => 'required|file|mimes:pdf',
        'project_id'    => 'required|exists:projects,id'
    ]);

    $projectId = $request->project_id;
    $sheetId = $request->sheet_id;

    try {
        // 1) Upload PDF temporarily
        $path = $request->file('checklist_pdf')->store('pdf_uploads');
        $fullPath = Storage::path($path);

        // 2) Run Python script
        $scriptPath = base_path('python/extract_pdf_fields.py');

        $process = new Process([
            'python',
            $scriptPath,
            $fullPath
        ]);

        $process->setTimeout(300);
        $process->run();

        // Remove uploaded file
        Storage::delete($path);

        if (!$process->isSuccessful()) {
            return back()->with('error', 'PDF processing failed.');
        }

        // 3) Parse JSON output
        $output = json_decode($process->getOutput(), true);

        if (!$output || isset($output['error'])) {
            return back()->with('error', 'Could not parse PDF.');
        }

        $savedCount = 0;

        // 4) Loop through extracted items
        foreach ($output as $description => $values) {

            $cleanDesc = trim($description);

            // Skip empty or invalid descriptions
            if (!$cleanDesc || strlen($cleanDesc) < 3) {
                continue;
            }

            $applicable   = $values["applicable"]   ?? false;
            $incorporated = $values["incorporated"] ?? false;
            $confirmed    = $values["confirmed"]    ?? false;

            // Skip rows where all checkboxes = false
            if (!$applicable && !$incorporated && !$confirmed) {
                continue;
            }

            // -----------------------------
            // 5) Create or locate QA Item Template
            // -----------------------------
     /*       $qaItem = \App\Models\QAItem::firstOrCreate(
                [
                    'item_description' => $cleanDesc
                ],
                [
                    'sheet_id' => $sheetId,
                ]
            );
*/
                $qaItem = QAItem::where('item_description', $description)
    ->where('sheet_id', $sheetId)
    ->first();

if (!$qaItem) {
    $qaItem = QAItem::create([
        'item_description' => $description,
        'sheet_id' => $sheetId,
    ]);
}

            // -----------------------------
            // 6) Insert project-specific status
            // -----------------------------
            \App\Models\ProjectQAItemStatus::create([
                'project_id'    => $projectId,
                'qa_item_id'    => $qaItem->id,

                'applicable'    => $applicable,
                'incorporated'  => $incorporated,
                'confirmed'     => $confirmed,

                'comments'      => null,
                'due_date'      => null,
                'assigned_to'   => null,
            ]);

            
            $savedCount++;
        }

        if ($savedCount > 0) {
            return back()->with('success', "{$savedCount} checklist items imported.");
        }

        return back()->with('error', 'No valid items found in this PDF.');

    } catch (\Exception $e) {
        return back()->with('error', "Error: " . $e->getMessage());
    }
}

public function uploadExcelAndSave(Request $request)
{
    set_time_limit(300);

    $request->validate([
        'excel_file' => 'required|file|mimes:xlsx,xls',
        'project_id' => 'required|exists:projects,id',
        'phase_id'   => 'required|exists:phases,id',
        'sheet_id'   => 'required|exists:sheets,id',
    ]);

    $projectId = $request->project_id;
    $phaseId   = $request->phase_id;
    $sheetId   = $request->sheet_id;

    try {
        // Load spreadsheet directly
        $filePath = $request->file('excel_file')->getRealPath();
        $spreadsheet = IOFactory::load($filePath);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $savedCount = 0;

        foreach ($rows as $index => $row) {

            // Skip header row
            if ($index === 1) continue;

            // Columns (adjust if needed)
            $applicable   = filter_var($row['A'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $incorporated = filter_var($row['B'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $confirmed    = filter_var($row['C'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Skip if all FALSE
            if (!$applicable && !$incorporated && !$confirmed) {
                continue;
            }

            // Build description (Topic - Category - Item - Notes)
            $topic    = trim($row['D'] ?? '');
            $category = trim($row['E'] ?? '');
            $item     = trim($row['F'] ?? '');
            $notes    = trim($row['G'] ?? '');

            $description = implode(' - ', array_filter([
                $topic, $category, $item, $notes
            ]));

            if (strlen($description) < 3) {
                continue;
            }

            // Create or find QA Item template
       /*     $qaItem = QAItem::firstOrCreate(
                [
                    'item_description' => $description
                ],
                [
                    'sheet_id' => $sheetId,
                ]
            );
*/
            $qaItem = QAItem::where('item_description', $description)
    ->where('sheet_id', $sheetId)
    ->first();

if (!$qaItem) {
    $qaItem = QAItem::create([
        'item_description' => $description,
        'sheet_id' => $sheetId,
    ]);
}


            // Create project-specific status (same as PDF logic)
            ProjectQAItemStatus::create([
                'project_id'   => $projectId,
                'qa_item_id'   => $qaItem->id,
                'applicable'   => $applicable,
                'incorporated' => $incorporated,
                'confirmed'    => $confirmed,
                'comments'     => null,
                'due_date'     => null,
                'assigned_to'  => null,
            ]);

            $savedCount++;
        }

        return back()->with('success', "{$savedCount} Excel checklist items imported!");

    } catch (\Exception $e) {
        Log::error("Excel Import Error: " . $e->getMessage());
        return back()->with('error', "Error: " . $e->getMessage());
    }
}



    private function convertFlatToRows($fields)
    {
        $rows = [];
        $currentItem = null;

        foreach ($fields as $key => $value) {

            // Checkbox field
            if (preg_match('/^Check Box/i', $key)) {

                if ($currentItem !== null) {
                    $rows[] = $currentItem;
                }

                $currentItem = [
                    'item_description' => '(Missing Item Text)',
                    'status' => $value ? 'in progress' : 'pending'
                ];
            }

            // Text line (Actual checklist item)
            else {
                if ($currentItem === null) {
                    $currentItem = [
                        'item_description' => $key,
                        'status' => $value ? 'in progress' : 'pending'
                    ];
                } else {
                    $currentItem['item_description'] = $key;
                }
            }
        }

        if ($currentItem !== null) {
            $rows[] = $currentItem;
        }

        return $rows;
    }
}

