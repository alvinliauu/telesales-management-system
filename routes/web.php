<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\SettingsController;

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Telesales - Renewal
    Route::prefix('telesales/renewal')->name('telesales.renewal.')->group(function () {
        Route::get('/', [RenewalController::class, 'index'])->name('index');
        Route::get('/upload', [RenewalController::class, 'showUpload'])->name('upload');
        Route::post('/upload', [RenewalController::class, 'upload'])->name('upload.process');
        Route::get('/next-call', [RenewalController::class, 'nextCall'])->name('next-call');
        Route::get('/{renewal}', [RenewalController::class, 'show'])->name('show');
        Route::patch('/{renewal}/call', [RenewalController::class, 'updateCall'])->name('update-call');
    });

    // Telesales - Upgrade (placeholder)
    Route::get('/telesales/upgrade', function () {
        return view('telesales.upgrade.index');
    })->name('telesales.upgrade.index');

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/create', [SettingsController::class, 'create'])->name('create');
        Route::post('/', [SettingsController::class, 'store'])->name('store');
        Route::get('/{event}/edit', [SettingsController::class, 'edit'])->name('edit');
        Route::put('/{event}', [SettingsController::class, 'update'])->name('update');
        Route::delete('/{event}', [SettingsController::class, 'destroy'])->name('destroy');
        Route::patch('/{event}/toggle-status', [SettingsController::class, 'toggleStatus'])->name('toggle-status');
    });
});
