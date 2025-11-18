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

Route::get('/qa-items/all', [QaItemController::class, 'indexAll'])->name('qa_items.indexAll');
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

            // Import (form + preview)
            Route::get('qa-items/import', [QaItemController::class, 'importForm'])
                ->name('qa_items.importForm');
            Route::post('qa-items/import-preview', [QaItemController::class, 'importPreview'])
                ->name('qa_items.importPreview');

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

// backup home route (لو لسه بتستخدمه)
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');
