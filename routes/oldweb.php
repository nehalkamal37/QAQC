<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PhaseController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
    use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SheetController;
use App\Http\Controllers\QaItemController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// صفحة ثابتة (اختيارية)
Route::get('/s', function () {
    return view('static/index');
});


Route::get('/profile', function () {
    return view('static.profile', [
        'user' => auth()->user()
    ]);
})->name('profile')->middleware('auth');

// كل حاجة داخل الـ web middleware مباشرة


// ======================
// ✅ نظام Authentication
// ======================
require __DIR__ . '/auth.php';

//electrecal qc checklist demo route
Route::post('/sheets/{sheet}/generate-from-master', [SheetController::class, 'generateFromMaster'])
    ->name('sheets.generateFromMaster');


Route::get('/demo/qc-checklist', [App\Http\Controllers\DemoController::class, 'qcChecklistDemo'])->name('demo.qc_checklist');

Route::get('qa-items/import/{sheetId}', [App\Http\Controllers\QAItemController::class, 'importForm'])
    ->name('qa_items.importForm');

Route::post('qa-items/import/{sheetId}', [App\Http\Controllers\QAItemController::class, 'importPreview'])
    ->name('qa_items.importPreview');

//status breakdown route for qaitems dashboard
Route::post('qa-items/{id}/status', [QaItemController::class, 'updateStatus'])
    ->name('qa_items.updateStatus');


// Dashboard عام for roles
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


Route::get('/phases/all', [PhaseController::class, 'indexAll'])->name('phases.indexAll');
Route::get('/sheets/all', [SheetController::class, 'indexAll'])->name('sheets.indexAll');
Route::get('/qa-items/all', [QaItemController::class, 'indexAll'])->name('qa_items.indexAll');

// ======================
// ✅ الصفحات المحمية
// ======================

// فقط الـ Admin و PM يقدروا يعملوا Create Project

Route::middleware(['auth', 'role:Admin,PM,Reviewer'])->group(function () {

    // Dashboard بعد تسجيل الدخول
    //Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');

    // الصفحة الرئيسية تفتح على قائمة المشاريع
    Route::get('/', [ProjectController::class, 'index'])->name('home');

    // Projects CRUD
    Route::resource('projects', ProjectController::class);

    // Phases داخل كل Project
    Route::prefix('projects/{projectId}')->group(function () {
        Route::get('phases', [PhaseController::class, 'index'])->name('phases.index');
        Route::get('phases/create', [PhaseController::class, 'create'])->name('phases.create');
        Route::post('phases', [PhaseController::class, 'store'])->name('phases.store');
    });

    // باقي عمليات الـ CRUD الخاصة بالـ Phases
    Route::resource('phases', PhaseController::class)->except(['index', 'create', 'store']);

    // review QA Items من قبل الـ Reviewer و PM

    Route::post('/qa-items/{id}/review', [QAItemController::class, 'addReview'])->name('qa_items.addReview');
Route::post('/sheets/{sheetId}/items/{qaItemId}/review', [QAItemController::class, 'addReview'])
    ->name('qa_items.addReview');


    // Existing resource routes
Route::resource('sheets.qa_items', QaItemController::class);

// ✅ Add review route
Route::post('/qa-items/{qaItemId}/review', [QaItemController::class, 'addReview'])->name('qa_items.addReview');
Route::post('/qa-items/{review}/update', [QaItemController::class, 'updateReview'])->name('qa_items.updateReview');
Route::delete('/qa-items/{review}/delete', [QaItemController::class, 'deleteReview'])->name('qa_items.deleteReview');


// ✅ Verify all route
Route::post('/sheets/{sheet}/qa-items/verify-all', [App\Http\Controllers\QaItemController::class, 'verifyAll'])
    ->name('qa_items.verifyAll');

// ✅ Resolve item route
Route::post('/sheets/{sheet}/qa-items/{qaItem}/resolve', [App\Http\Controllers\QaItemController::class, 'resolve'])
    ->name('qa_items.resolve');

});


// Reviewer و PM بس يقدروا يقفلوا QA Items
Route::middleware(['auth', 'role:Reviewer,PM'])->group(function () {
    Route::post('/qa-items/{id}/verify', [QaItemController::class, 'verify'])->name('qa_items.verify');
});


Route::prefix('phases/{phaseId}')->group(function () {
    Route::get('sheets', [App\Http\Controllers\SheetController::class, 'index'])->name('sheets.index');
    Route::get('sheets/create', [App\Http\Controllers\SheetController::class, 'create'])->name('sheets.create');
    Route::post('sheets', [App\Http\Controllers\SheetController::class, 'store'])->name('sheets.store');
});
Route::resource('sheets', App\Http\Controllers\SheetController::class)->except(['index', 'create', 'store']);



Route::middleware(['auth', 'role:Reviewer,PM,Admin'])->group(function () {

Route::prefix('sheets/{sheetId}')->group(function () {
    Route::get('qa-items', [App\Http\Controllers\QAItemController::class, 'index'])->name('qa_items.index');
    Route::get('qa-items/create', [App\Http\Controllers\QAItemController::class, 'create'])->name('qa_items.create');
    Route::post('qa-items', [App\Http\Controllers\QAItemController::class, 'store'])->name('qa_items.store');
    Route::get('qa-items/{qaItem}/edit', [App\Http\Controllers\QAItemController::class, 'edit'])->name('qa_items.edit');
    Route::put('qa-items/{qaItem}', [App\Http\Controllers\QAItemController::class, 'update'])->name('qa_items.update');
    Route::delete('qa-items/{qaItem}', [App\Http\Controllers\QAItemController::class, 'destroy'])->name('qa_items.destroy');
});

});


 //Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
