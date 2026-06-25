<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\UpdatePasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\UpdateProfileController;
use Illuminate\Support\Facades\Route;


// --- مسارات الضيوف (Guest) ---
Route::middleware('guest')->group(function () {

    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->name('register');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/verify-otp', [VerifyEmailController::class, 'verifyOtp'])
        ->name('verification.verify');
    Route::post('/resend-otp', [RegisteredUserController::class, 'resendOtp'])
        ->name('verification.resend');
        });
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::put('/profile/update', [UpdateProfileController::class, 'updateProfile']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
    Route::post('/change-password', [UpdatePasswordController::class, 'update'])
    ->name('password.change');

});