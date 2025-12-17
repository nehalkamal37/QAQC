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
use App\Http\Controllers\QcUploadController;
use App\Http\Controllers\PhaseKanbanController;
use App\Http\Controllers\ReportScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\Project;
use App\Models\Phase;
use App\Http\Controllers\WeeklyReportController;
// ======================
// Auth routes
// ======================
require __DIR__ . '/auth.php';

// صفحة ثابتة اختيارية
Route::get('/s', function () {
    return view('static.index');
});

Route::get('/profile', function () {
    return view('static.profile', [
        'user' => auth()->user()
    ]);
})->name('profile')->middleware('auth');

// Demo route
Route::get('/demo/qc-checklist', [DemoController::class, 'qcChecklistDemo'])
    ->name('demo.qc_checklist');

// ======================
// كل ما هو محمي بالـ auth
// ======================
Route::middleware('auth')->group(function () {

    // Dashboard عام
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // الهوم تفتح على المشاريع
    Route::get('/', [ProjectController::class, 'index'])
        ->name('home');

    // شاشات الـ overview
    Route::get('/phases/all', [PhaseController::class, 'indexAll'])->name('phases.indexAll');
    Route::get('/sheets/all', [SheetController::class, 'indexAll'])->name('sheets.indexAll');
    Route::get('/qa-items/all', [QaItemController::class, 'indexAll'])->name('qa_items.indexAll');

    // sunday workflow routes
    Route::patch('/phases/{phase}/status', [PhaseStatusController::class, 'update'])
     ->name('phases.status.update');

     // attachments routes
     Route::post('/qa-items/{item}/attachments', [AttachmentController::class, 'store'])
    ->name('attachments.store');

Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy'])
    ->name('attachments.destroy');

    Route::post('/sheets/{sheet}/attachments', [AttachmentController::class, 'storeForSheet'])
     ->name('attachments.store.sheet');

// timeline page route
Route::get('/timeline', [\App\Http\Controllers\TimelineController::class, 'index'])
    ->name('timeline.index')
    ->middleware('auth');

Route::get('/reviews', [QaItemController::class, 'reviewsIndex'])->name('qa_reviews.index');
// routes/web.php
   //Route::get('/reviews', [QaItemController::class, 'reviewsIndex'])->name('reviews.index');

Route::delete('/timeline/{id}', [\App\Http\Controllers\TimelineController::class, 'destroy'])
    ->name('timeline.destroy');


    // ======================
    // صلاحيات Admin / PM / Reviewer
    // ======================
    Route::middleware([])->group(function () {

        // Projects CRUD
        Route::resource('projects', ProjectController::class);

        // Phases داخل كل Project (index/create/store)
        Route::prefix('projects/{projectId}')->group(function () {
            Route::get('phases', [PhaseController::class, 'index'])->name('phases.index');
            Route::get('phases/create', [PhaseController::class, 'create'])->name('phases.create');
            Route::post('phases', [PhaseController::class, 'store'])->name('phases.store');
        });

        // باقي عمليات الـ Phases
        Route::resource('phases', PhaseController::class)
            ->except(['index', 'create', 'store']);

        // ======================
        // Sheets routes
        // ======================

        // Sheets داخل Phase (index/create/store)
        Route::prefix('phases/{phaseId}')->group(function () {
            Route::get('sheets', [SheetController::class, 'index'])->name('sheets.index');
            Route::get('sheets/create', [SheetController::class, 'create'])->name('sheets.create');
            Route::post('sheets', [SheetController::class, 'store'])->name('sheets.store');
        });

        // باقي عمليات الـ Sheets
        Route::resource('sheets', SheetController::class)
            ->except(['index', 'create', 'store']);

        // Generate QA Items from Master checklist
        Route::post('/sheets/{sheet}/generate-from-master', [SheetController::class, 'generateFromMaster'])
            ->name('sheets.generateFromMaster');

        // ======================
        // QA ITEMS (nested تحت sheet)
        // ======================
        Route::prefix('sheets/{sheetId}')->group(function () {

            // Index / Create / Store / Edit / Update / Delete
            Route::get('qa-items', [QaItemController::class, 'index'])->name('qa_items.index');
            Route::get('qa-items/create', [QaItemController::class, 'create'])->name('qa_items.create');
            Route::post('qa-items', [QaItemController::class, 'store'])->name('qa_items.store');
            Route::get('qa-items/{qaItem}/edit', [QaItemController::class, 'edit'])->name('qa_items.edit');
            Route::put('qa-items/{qaItem}', [QaItemController::class, 'update'])->name('qa_items.update');
            Route::delete('qa-items/{qaItem}', [QaItemController::class, 'destroy'])->name('qa_items.destroy');
            Route::get('/qa-items/{qa_item}', [App\Http\Controllers\QaItemController::class, 'show'])->name('qa_items.show');

            // Import (form + preview)
            Route::get('qa-items/import', [QaItemController::class, 'importForm'])
                ->name('qa_items.importForm');
            Route::post('qa-items/import-preview', [QaItemController::class, 'importPreview'])
                ->name('qa_items.importPreview');
            Route::post('qa-items/import-confirm', [QaItemController::class, 'importConfirm'])
            ->name('qa_items.importConfirm');


// PDF Import Routes
/*
Route::get('qa-items/import-pdf', [QaItemController::class, 'importPdfForm'])
    ->name('qa_items.importPdfForm');

Route::post('qa-items/import-pdf-preview', [QaItemController::class, 'importPdfPreview'])
    ->name('qa_items.importPdfPreview');

Route::post('qa-items/import-pdf-confirm', [QaItemController::class, 'importPdfConfirm'])
    ->name('qa_items.importPdfConfirm');

Route::post('/sheets/{sheet}/qa-items/import-pdf-preview', [QaItemController::class, 'importPdfPreview'])
    ->name('qa_items.importPdfPreview');
*/

// PDF Import Routes




            // Verify all items داخل نفس الـ sheet
            Route::post('qa-items/verify-all', [QaItemController::class, 'verifyAll'])
                ->name('qa_items.verifyAll');

            // Resolve single item
            Route::post('qa-items/{qaItem}/resolve', [QaItemController::class, 'resolve'])
                ->name('qa_items.resolve');

            // ✅ Add review (هنا بنبعت sheetId + qaItemId)
            Route::post('qa-items/{qaItemId}/review', [QaItemController::class, 'addReview'])
                ->name('qa_items.addReview');
        });

        // ======================
        // Routes إضافية للـ QA Items
        // ======================

        // Quick status update من الـ Dashboard أو أي مكان عام
        Route::post('qa-items/{id}/status', [QaItemController::class, 'updateStatus'])
            ->name('qa_items.updateStatus');

        // Update / Delete Review (بالـ review id فقط)
        Route::post('qa-item-reviews/{review}/update', [QaItemController::class, 'updateReview'])
            ->name('qa_items.updateReview');

        Route::delete('qa-item-reviews/{review}', [QaItemController::class, 'deleteReview'])
            ->name('qa_items.deleteReview');
    });

    // لو عندك verify مخصصة
    Route::middleware(['role:Reviewer,PM'])->group(function () {
        Route::post('/qa-items/{id}/verify', [QaItemController::class, 'verify'])
            ->name('qa_items.verify');
    });
});


// ============================
// PDF IMPORT ROUTES (GLOBAL)
// ============================

Route::get('/sheet/{sheet}/qa/import-pdf',
    [QaItemController::class, 'importPdfForm'])
    ->name('qa_items.importPdfForm');

Route::post('/sheet/{sheet}/qa/import-pdf-preview',
    [QaItemController::class, 'importPdfPreview'])
    ->name('qa_items.importPdfPreview');

Route::post('/sheet/{sheet}/qa/import-pdf-confirm',
    [QaItemController::class, 'importPdfConfirm'])
    ->name('qa_items.importPdfConfirm');

// backup home route (لو لسه بتستخدمه)
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');




    // assignments routes
Route::resource('assignments', AssignmentController::class);
Route::post('assignments/bulk', [AssignmentController::class, 'bulkAssign'])->name('assignments.bulk');



// my work dashboard route
Route::get('/my-work', [DashboardController::class, 'myWork'])->name('dashboard.my-work');
// routes/web.php


// assign to me route if item not assigned to anyone
Route::post('/qa-items/{qa_item}/assign-to-me', [QaItemController::class, 'assignToMe'])
    ->name('qa_items.assign-to-me')
    ->middleware('auth');

Route::patch('/qa-items/{qa_item}/update-status', [QaItemController::class, 'updateStatusAssigned'])->name('qa_items.update-status');

// pdf checklist upload route
Route::post('/upload-checklist', [ChecklistController::class, 'uploadChecklist'])->name('upload.checklist');
Route::get('/checked-items/{projectNumber}', [ChecklistController::class, 'getCheckedItems']);

// routes/web.php
Route::get('/test-system', [ChecklistController::class, 'testSystem']);

Route::get('/test-sample-data', [ChecklistController::class, 'testWithSampleData']);

Route::get('/debug-pdf-text', [ChecklistController::class, 'debugPdfText']);

Route::post('/debug-pdf', [ChecklistController::class, 'debugPdfExtraction']);

Route::get('/debug-form', [ChecklistController::class, 'debugPdfForm']);

Route::post('/test-mapping', [ChecklistController::class, 'testMapping']);


// notifications routes
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});


// routes/web.php - Add this temporary route for testing
Route::get('/test-notifications', function () {
    
    // Create test notifications for current user
    NotificationService::send(
        auth()->id(),
        'test',
        'Test Notification',
        'This is a test notification to verify the system is working.'
    );
    
    NotificationService::notifyAssignment(
        auth()->id(),
        'Test Project',
        'Engineer',
        'System Admin'
    );

    return redirect('/')->with('success', 'Test notifications created!');
});


// routes/web.php - Update the test route
Route::get('/test-due-date-notification', function () {
    
    // Get a real QA item with due date, or create a test one
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
        // Create a test item with due date
        $qaItem = \App\Models\QaItem::first();
        if ($qaItem) {
            $qaItem->update(['due_date' => now()->addDays(2)]);
            $daysUntilDue = 2;
            
            NotificationService::notifyDueDateReminder(
                auth()->id(),
                $qaItem->id,
                $daysUntilDue
            );
            
            return redirect('/')->with('success', "Test due date notification created! Item due in 2 days");
        }
    }
    
    return redirect('/')->with('error', 'No QA items found to test due dates');
});


// routes/api.php


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





    // Kanban Board Routes

Route::middleware(['auth'])->group(function () {
    // Kanban Board Routes
    Route::get('/phases/{phase}/kanban', [PhaseKanbanController::class, 'show'])->name('phases.kanban');
    Route::patch('/qa-items/{qaItem}/status', [PhaseKanbanController::class, 'updateQaItemStatus'])->name('qa-items.status.update');
    Route::post('/phases/{phase}/kanban/bulk-update', [PhaseKanbanController::class, 'bulkUpdate'])->name('phases.kanban.bulk-update');
    
    // Add Kanban link to your existing phases index
    Route::get('/projects/{project}/phases', [PhaseController::class, 'index'])->name('phases.index');

    Route::get('/qa-items/{qaItem}/details', [PhaseKanbanController::class, 'getItemDetails'])->name('qa-items.details');
});

// routes/web.php
Route::get('/read-pdf', [App\Http\Controllers\PdfController::class, 'read']);


Route::get('/pdf/python', [App\Http\Controllers\PdfController::class, 'readWithPython']);

Route::get('py', function () {
    return view('python.extract_pdf_fields.py');
});



// pdf from python code 



// PDF Upload and Processing Routes



// Remove any duplicate routes and use this:
Route::post('/upload-checklist', [ChecklistController::class, 'uploadAndSave'])->name('checklist.upload.save');

// Remove any other checklist routes that might be conflicting


      // pdf upload routes
Route::post('/checklist/preview', [ChecklistController::class, 'preview'])
     ->name('checklist.preview');

     /* needs delete
Route::post('/checklist/import/{sheet}', [ChecklistController::class, 'import'])
     ->name('qa_items.importPdfConfirm');\*/

Route::post('/checklist/upload/save', [ChecklistController::class, 'uploadAndSave'])
    ->name('checklist.upload.save');

    Route::get('/upload-checklist', [ChecklistController::class, 'showForm'])
    ->name('checklist.upload');

    // csv upload routes

    Route::post('/checklist/upload-excel', [ChecklistController::class, 'uploadExcelAndSave'])
    ->name('checklist.upload.excel.save');


    Route::get('/upload-checklist2', [ChecklistController::class, 'showForm2'])
    ->name('checklist.upload.csv');

    // routes/web.php
Route::get('/checklist/upload-excel', [ChecklistController::class, 'showForm2'])
    ->name('checklist.upload.excel.form');


    

Route::get('/projects/{project}/phases', function (Project $project) {
    return $project->phases()->select('id', 'type')->get();
});

Route::get('/phases/{phase}/sheets', function (Phase $phase) {
    return $phase->sheets()->select('id', 'number', 'title')->get();
});

// dashboard qa progress route

Route::get('/analytics/project-progress', [DashboardController::class, 'ajaxProjectProgress']);

Route::get('/analytics/project-phases', function (Request $r) {
    return \App\Models\Phase::where('project_id', $r->project_id)
        ->select('id', 'type')
        ->orderBy('id')
        ->get();
});

    Route::get('/test-email', function () {
    return \App\Services\NotificationService::notifyAssignment(
        1,                      // user_id
        "Project ABC",          // project name
        "Developer",            // role
        "Admin"                 // assigned by
    );
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

// Dashboard QA Trend Endpoint


// Dashboard Phase Progress Endpoint

Route::get('/analytics/qa-trend', [DashboardController::class, 'qaTrend'])->name('analytics.qa-trend');

Route::get('/analytics/sheet-heatmap', [DashboardController::class, 'sheetStatusHeatmap'])
    ->name('analytics.sheet-heatmap');

Route::get('/analytics/phase-gates', [DashboardController::class, 'phaseGateAnalytics'])
    ->name('analytics.phase-gates');

Route::get('/analytics/sla', [DashboardController::class, 'slaAnalytics'])
    ->name('analytics.sla');
   
Route::get('/analytics/qa-trend-project', [DashboardController::class, 'qaTrendProject'])
    ->name('analytics.qa-trend-project');

    Route::get('/phases/by-project/{project}', function ($projectId) {
    return \App\Models\Phase::where('project_id', $projectId)
        ->orderBy('type')
        ->get(['id', 'type']);
});


Route::get('/analytics/qa-items', [DashboardController::class, 'getQaItems']);

Route::get('/analytics/phase-burndown', 
    [DashboardController::class, 'phaseBurndown'])
    ->name('analytics.phase-burndown');


    // Test route for debugging
Route::get('/test-qa-items', function() {
    $sheetId = request('sheet_id', 3);
    $status = request('status', 'open');
    
    $items = \App\Models\QaItem::where('sheet_id', $sheetId)
        ->with(['sheet', 'assigneeUser', 'projectStatus'])
        ->get();
    
        return dd($items);


});








/*  the right routes  


// 1) Upload checklist (PDF/Excel) - 2-step: preview then save
Route::get('/upload-checklist', [ChecklistController::class, 'showForm'])
    ->name('checklist.upload.form');

Route::post('/checklist/preview', [ChecklistController::class, 'preview'])
    ->name('checklist.preview');

Route::post('/checklist/upload/save', [ChecklistController::class, 'uploadAndSave'])
    ->name('checklist.upload.save');

// 2) Import PDF to specific sheet (QA Items)
Route::get('/sheet/{sheet}/qa/import-pdf', [QaItemController::class, 'importPdfForm'])
    ->name('qa_items.importPdfForm');

Route::post('/sheet/{sheet}/qa/import-pdf-preview', [QaItemController::class, 'importPdfPreview'])
    ->name('qa_items.importPdfPreview');

Route::post('/sheet/{sheet}/qa/import-pdf-confirm', [QaItemController::class, 'importPdfConfirm'])
    ->name('qa_items.importPdfConfirm');
*/

// ======================
// QA ITEMS (nested تحت Sheet)
// ======================
Route::middleware('auth')->group(function () {

    Route::prefix('sheets/{sheet}')->group(function () {

        // CRUD
        Route::get('qa-items', [QaItemController::class, 'index'])->name('qa_items.index');
        Route::get('qa-items/create', [QaItemController::class, 'create'])->name('qa_items.create');
        Route::post('qa-items', [QaItemController::class, 'store'])->name('qa_items.store');
        Route::get('qa-items/{qaItem}/edit', [QaItemController::class, 'edit'])->name('qa_items.edit');
        Route::put('qa-items/{qaItem}', [QaItemController::class, 'update'])->name('qa_items.update');
        Route::delete('qa-items/{qaItem}', [QaItemController::class, 'destroy'])->name('qa_items.destroy');

        // Import from CSV
        Route::get('qa-items/import', [QaItemController::class, 'importForm'])->name('qa_items.importForm');
        Route::post('qa-items/import-preview', [QaItemController::class, 'importPreview'])->name('qa_items.importPreview');
        Route::post('qa-items/import-confirm', [QaItemController::class, 'importConfirm'])->name('qa_items.importConfirm');

        // Verify all items in sheet
        Route::post('qa-items/verify-all', [QaItemController::class, 'verifyAll'])->name('qa_items.verifyAll');

        // Resolve
        Route::post('qa-items/{qaItem}/resolve', [QaItemController::class, 'resolve'])->name('qa_items.resolve');

        // Add review
        Route::post('qa-items/{qaItem}/review', [QaItemController::class, 'addReview'])->name('qa_items.addReview');
    });

    // Global list & show
    Route::get('/qa-items/all', [QaItemController::class, 'indexAll'])->name('qa_items.indexAll');
    Route::get('/qa-items/{qaItem}', [QaItemController::class, 'show'])->name('qa_items.show');

    // Quick status update – SINGLE source of truth
    Route::patch('/qa-items/{qaItem}/status', [QaItemController::class, 'updateStatus'])
        ->name('qa_items.status.update');

    // Assign to me
    Route::post('/qa-items/{qaItem}/assign-to-me', [QaItemController::class, 'assignToMe'])
        ->name('qa_items.assign-to-me');

    // Reviews update/delete
    Route::post('qa-item-reviews/{review}/update', [QaItemController::class, 'updateReview'])
        ->name('qa_items.updateReview');
    Route::delete('qa-item-reviews/{review}', [QaItemController::class, 'deleteReview'])
        ->name('qa_items.deleteReview');

    // Verify (restricted roles)
    Route::middleware(['role:Reviewer,PM'])->group(function () {
        Route::post('/qa-items/{qaItem}/verify', [QaItemController::class, 'verify'])
            ->name('qa_items.verify');
    });

    // for report scheduling
    Route::get('/settings/reports', [ReportScheduleController::class, 'edit'])->name('settings.reports.edit');
    Route::post('/settings/reports', [ReportScheduleController::class, 'update']);


    // for pdf/csv report download
    Route::get('/reports/weekly', [WeeklyReportController::class, 'show'])->name('reports.weekly');
Route::get('/reports/weekly/pdf', [WeeklyReportController::class, 'pdf']);
Route::get('/reports/weekly/csv', [WeeklyReportController::class, 'csv']);

});
