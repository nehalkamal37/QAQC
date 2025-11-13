<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::middleware(['web'])->group(function () {
    // 🟢 Register
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->middleware('guest')->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('guest');

    // 🟢 Login
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware('guest')->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest');

    // 🟢 Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')->name('logout');

    // 🟡 Forgot Password
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->middleware('guest')->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('guest')->name('password.email');

    // 🟡 Reset Password
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->middleware('guest')->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('guest')->name('password.store');
});
