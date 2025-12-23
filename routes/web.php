<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PhaseController;
use App\Http\Controllers\SheetController;
use App\Http\Controllers\QaItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\PhaseStatusController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ChecklistController;
use App\Services\NotificationService;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PhaseKanbanController;
use App\Http\Controllers\ReportScheduleController;
use App\Http\Controllers\WeeklyReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Project;
use App\Models\Phase;

// ======================
// Auth routes
// ======================
require __DIR__ . '/auth.php';

// ======================
// Public Routes
// ======================
Route::get('/s', function () {
    return view('static.index');
});

Route::get('/demo/qc-checklist', [DemoController::class, 'qcChecklistDemo'])
    ->name('demo.qc_checklist');

// ======================
// Authenticated Routes
// ======================
Route::middleware(['auth' , \App\Http\Middleware\NoCache::class])->group(function () {

    // Dashboard & Home
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [ProjectController::class, 'index'])->name('home');
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/my-work', [DashboardController::class, 'myWork'])->name('dashboard.my-work');

    // Profile
    Route::get('/profile', function () {
        return view('static.profile', ['user' => auth()->user()]);
    })->name('profile');

    // Overview Pages
    Route::get('/phases/all', [PhaseController::class, 'indexAll'])->name('phases.indexAll');
    Route::get('/sheets/all', [SheetController::class, 'indexAll'])->name('sheets.indexAll');
    Route::get('/qa-items/all', [QaItemController::class, 'indexAll'])->name('qa_items.indexAll');

    // Timeline
    Route::get('/timeline', [\App\Http\Controllers\TimelineController::class, 'index'])
        ->name('timeline.index');
    Route::delete('/timeline/{id}', [\App\Http\Controllers\TimelineController::class, 'destroy'])
        ->name('timeline.destroy');

    // Reviews
    Route::get('/reviews', [QaItemController::class, 'reviewsIndex'])->name('qa_reviews.index');

    // Phase Status Update
        Route::patch('/phases/{phase}/status', [PhaseStatusController::class, 'updateStatus'])
    ->name('phases.status.update');

    // ======================
    // Projects CRUD
    // ======================
    Route::resource('projects', ProjectController::class);

    // ======================
    // Phases Routes
    // ======================
    Route::prefix('projects/{projectId}')->group(function () {
        Route::get('phases', [PhaseController::class, 'index'])->name('phases.index');
        Route::get('phases/create', [PhaseController::class, 'create'])->name('phases.create');
        Route::post('phases', [PhaseController::class, 'store'])->name('phases.store');
    });

    Route::resource('phases', PhaseController::class)
        ->except(['index', 'create', 'store']);

    // Kanban Board
    Route::get('/phases/{phase}/kanban', [PhaseKanbanController::class, 'show'])->name('phases.kanban');
    Route::post('/phases/{phase}/kanban/bulk-update', [PhaseKanbanController::class, 'bulkUpdate'])
        ->name('phases.kanban.bulk-update');

    // ======================
    // Sheets Routes
    // ======================
    Route::prefix('phases/{phaseId}')->group(function () {
        Route::get('sheets', [SheetController::class, 'index'])->name('sheets.index');
        Route::get('sheets/create', [SheetController::class, 'create'])->name('sheets.create');
        Route::post('sheets', [SheetController::class, 'store'])->name('sheets.store');
    });

    Route::resource('sheets', SheetController::class)
        ->except(['index', 'create', 'store']);

    Route::post('/sheets/{sheet}/generate-from-master', [SheetController::class, 'generateFromMaster'])
        ->name('sheets.generateFromMaster');

    // Sheet Attachments
    Route::post('/sheets/{sheet}/attachments', [AttachmentController::class, 'storeForSheet'])
        ->name('attachments.store.sheet');

    // ======================
    // QA Items Routes (Nested under Sheet)
    // ======================
    Route::prefix('sheets/{sheet}')->group(function () {

        // CRUD

        Route::get('qa-items', [QaItemController::class, 'index'])->name('qa_items.index');
        Route::get('qa-items/create', [QaItemController::class, 'create'])->name('qa_items.create');
        Route::post('qa-items', [QaItemController::class, 'store'])->name('qa_items.store');
        Route::get('qa-items/{qaItem}/edit', [QaItemController::class, 'edit'])->name('qa_items.edit');
        Route::put('qa-items/{qaItem}', [QaItemController::class, 'update'])->name('qa_items.update');
        Route::delete('qa-items/{qaItem}', [QaItemController::class, 'destroy'])->name('qa_items.destroy');

        // CSV Import
        Route::get('qa-items/import', [QaItemController::class, 'importForm'])->name('qa_items.importForm');
        Route::post('qa-items/import-preview', [QaItemController::class, 'importPreview'])
            ->name('qa_items.importPreview');
        Route::post('qa-items/import-confirm', [QaItemController::class, 'importConfirm'])
            ->name('qa_items.importConfirm');

        // PDF Import
        Route::get('qa/import-pdf', [QaItemController::class, 'importPdfForm'])
            ->name('qa_items.importPdfForm');
        Route::post('qa/import-pdf-preview', [QaItemController::class, 'importPdfPreview'])
            ->name('qa_items.importPdfPreview');
        Route::post('qa/import-pdf-confirm', [QaItemController::class, 'importPdfConfirm'])
            ->name('qa_items.importPdfConfirm');

        // Bulk Operations
        Route::post('qa-items/verify-all', [QaItemController::class, 'verifyAll'])
            ->name('qa_items.verifyAll');

        // Resolve
        Route::post('qa-items/{qaItem}/resolve', [QaItemController::class, 'resolve'])
            ->name('qa_items.resolve');

        // Reviews
        Route::post('qa-items/{qaItem}/review', [QaItemController::class, 'addReview'])
            ->name('qa_items.addReview');
    });

    // QA Items - Global Routes
    Route::get('/qa-items/{qaItem}', [QaItemController::class, 'show'])->name('qa_items.show');
    Route::get('/qa-items/{qaItem}/details', [PhaseKanbanController::class, 'getItemDetails'])
        ->name('qa-items.details');

    // QA Items - Status Updates (Single Source of Truth)
    Route::patch('/qa-items/{qaItem}/status', [QaItemController::class, 'updateStatus'])
        ->name('qa_items.status.update');
    Route::patch('/qa-items/{qaItem}/update-status', [QaItemController::class, 'updateStatusAssigned'])
        ->name('qa_items.update-status');

    // QA Items - Assignments
    Route::post('/qa-items/{qaItem}/assign-to-me', [QaItemController::class, 'assignToMe'])
        ->name('qa_items.assign-to-me');

    // QA Items - Attachments
    Route::post('/qa-items/{item}/attachments', [AttachmentController::class, 'store'])
        ->name('attachments.store');
    Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy'])
        ->name('attachments.destroy');

    // QA Item Reviews
    Route::post('qa-item-reviews/{review}/update', [QaItemController::class, 'updateReview'])
        ->name('qa_items.updateReview');
    Route::delete('qa-item-reviews/{review}', [QaItemController::class, 'deleteReview'])
        ->name('qa_items.deleteReview');

    // QA Items - Verify (Restricted Roles)
    Route::middleware(['role:Reviewer,PM'])->group(function () {
        Route::post('/qa-items/{qaItem}/verify', [QaItemController::class, 'verify'])
            ->name('qa_items.verify');
    });

    // ======================
    // Assignments
    // ======================
    Route::resource('assignments', AssignmentController::class);
    Route::post('assignments/bulk', [AssignmentController::class, 'bulkAssign'])
        ->name('assignments.bulk');

    // ======================
    // Checklist Upload (PDF/Excel)
    // ======================
    Route::get('/upload-checklist', [ChecklistController::class, 'showForm'])
        ->name('checklist.upload');
    Route::get('/upload-checklist2', [ChecklistController::class, 'showForm2'])
        ->name('checklist.upload.csv');
    Route::get('/checklist/upload-excel', [ChecklistController::class, 'showForm2'])
        ->name('checklist.upload.excel.form');

    Route::post('/checklist/preview', [ChecklistController::class, 'preview'])
        ->name('checklist.preview');
    Route::post('/checklist/upload/save', [ChecklistController::class, 'uploadAndSave'])
        ->name('checklist.upload.save');
    Route::post('/checklist/upload-excel', [ChecklistController::class, 'uploadExcelAndSave'])
        ->name('checklist.upload.excel.save');

    // ======================
    // QC Upload
    // ======================
 /*
    Route::post('/qc-upload', [QcUploadController::class, 'upload']);
    Route::get('/qc-checklist', [QcUploadController::class, 'viewChecklist']);
    Route::get('/qc-test', function () {
        \App\Models\QcItem::truncate();
        \App\Models\QcItem::insert([
            [
                'item_text' => 'Find applicable codes and amendments',
                'applicable' => false,
                'incorporated' => false,
                'confirmed' => false,
            ],
            [
                'item_text' => 'Water supply flow test data incorporated into design',
                'applicable' => true,
                'incorporated' => true,
                'confirmed' => true,
            ],
            [
                'item_text' => 'Domestic Water Heater Schedule',
                'applicable' => false,
                'incorporated' => false,
                'confirmed' => false,
            ],
        ]);
        return 'Test data inserted! Go to /qc-checklist';
    });
*/
    // ======================
    // Notifications
    // ======================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])
            ->name('notifications.destroy');
    });

    // ======================
    // Analytics & Reports
    // ======================
    Route::prefix('analytics')->group(function () {
        Route::get('/project-progress', [DashboardController::class, 'ajaxProjectProgress']);
        Route::get('/project-phases', function (Request $r) {
            return Phase::where('project_id', $r->project_id)
                ->select('id', 'type')
                ->orderBy('id')
                ->get();
        });
        Route::get('/qa-trend', [DashboardController::class, 'qaTrend'])->name('analytics.qa-trend');
        Route::get('/qa-trend-project', [DashboardController::class, 'qaTrendProject'])
            ->name('analytics.qa-trend-project');
        Route::get('/sheet-heatmap', [DashboardController::class, 'sheetStatusHeatmap'])
            ->name('analytics.sheet-heatmap');
        Route::get('/phase-gates', [DashboardController::class, 'phaseGateAnalytics'])
            ->name('analytics.phase-gates');
        Route::get('/sla', [DashboardController::class, 'slaAnalytics'])
            ->name('analytics.sla');
        Route::get('/qa-items', [DashboardController::class, 'getQaItems']);
        Route::get('/phase-burndown', [DashboardController::class, 'phaseBurndown'])
            ->name('analytics.phase-burndown');
    });

    // Report Scheduling
    Route::get('/settings/reports', [ReportScheduleController::class, 'edit'])
        ->name('settings.reports.edit');
    Route::post('/settings/reports', [ReportScheduleController::class, 'update']);

    // Weekly Reports
    Route::get('/reports/weekly', [WeeklyReportController::class, 'show'])->name('reports.weekly');
    Route::get('/reports/weekly/pdf', [WeeklyReportController::class, 'pdf']);
    Route::get('/reports/weekly/csv', [WeeklyReportController::class, 'csv']);

    // ======================
    // Helper API Routes
    // ======================
    Route::get('/projects/{project}/phases', function (Project $project) {
        return $project->phases()->select('id', 'type')->get();
    });

    Route::get('/phases/{phase}/sheets', function (Phase $phase) {
        return $phase->sheets()->select('id', 'number', 'title')->get();
    });

    Route::get('/phases/by-project/{project}', function ($projectId) {
        return Phase::where('project_id', $projectId)
            ->orderBy('type')
            ->get(['id', 'type']);
    });

    // ======================
    // Testing & Debug Routes (Remove in Production)
    // ======================
    Route::prefix('test')->group(function () {
        Route::get('/system', [ChecklistController::class, 'testSystem']);
        Route::get('/sample-data', [ChecklistController::class, 'testWithSampleData']);
        Route::get('/debug-pdf-text', [ChecklistController::class, 'debugPdfText']);
        Route::post('/debug-pdf', [ChecklistController::class, 'debugPdfExtraction']);
        Route::get('/debug-form', [ChecklistController::class, 'debugPdfForm']);
        Route::post('/mapping', [ChecklistController::class, 'testMapping']);

        Route::get('/notifications', function () {
            NotificationService::send(
                auth()->id(),
                'test',
                'Test Notification',
                'This is a test notification to verify the system is working.'
            );
            NotificationService::notifyAssignment(
                auth()->id(),
                "Test Project",
                "Engineer",
                "System Admin"
            );
            return redirect('/')->with('success', 'Test notifications created!');
        });

        Route::get('/due-date-notification', function () {
            $qaItem = \App\Models\QaItem::whereNotNull('due_date')->first();
            if ($qaItem && $qaItem->due_date) {
                $daysUntilDue = $qaItem->due_date->diffInDays(now());
                NotificationService::notifyDueDateReminder(
                    auth()->id(),
                    $qaItem->id,
                    $daysUntilDue
                );
                return redirect('/')->with('success', "Due date notification created! Item due in {$daysUntilDue} days");
            } else {
                $qaItem = \App\Models\QaItem::first();
                if ($qaItem) {
                    $qaItem->update(['due_date' => now()->addDays(2)]);
                    NotificationService::notifyDueDateReminder(auth()->id(), $qaItem->id, 2);
                    return redirect('/')->with('success', "Test due date notification created! Item due in 2 days");
                }
            }
            return redirect('/')->with('error', 'No QA items found to test due dates');
        });

        Route::get('/email', function () {
            return NotificationService::notifyAssignment(1, "Project ABC", "Developer", "Admin");
        });

        Route::get('/mailtest', function () {
            try {
                Mail::raw('Hello from Mailtrap!', function ($message) {
                    $message->to('nehalk751@gmail.com')->subject('SMTP Test');
                });
                return "Mail sent!";
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });

        Route::get('/qa-items', function() {
            $sheetId = request('sheet_id', 3);
            $status = request('status', 'open');
            $items = \App\Models\QaItem::where('sheet_id', $sheetId)
                ->with(['sheet', 'assigneeUser', 'projectStatus'])
                ->get();
            return dd($items);
        });
    });

    // PDF Routes
    Route::get('/read-pdf', [App\Http\Controllers\PdfController::class, 'read']);
    Route::get('/pdf/python', [App\Http\Controllers\PdfController::class, 'readWithPython']);
    Route::get('py', function () {
        return view('python.extract_pdf_fields.py');
    });



    // missed roues after deploy
     Route::post('qa-items/{id}/status', [QaItemController::class, 'updateStatus'])
            ->name('qa_items.updateStatus');



});

// ======================
// Fallback Route
// ======================
Route::fallback(function () {
    return auth()->check() 
        ? redirect()->route('home') 
        : redirect()->route('login');
});
