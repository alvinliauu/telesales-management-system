<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CarTypeController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\EventPackageController;

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

    // ============================================
    // TELESALES EVENT SETUP
    // ============================================
    
    // Events (renamed from settings)
    Route::prefix('events')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/create', [SettingsController::class, 'create'])->name('create');
        Route::post('/', [SettingsController::class, 'store'])->name('store');
        Route::get('/{event}/edit', [SettingsController::class, 'edit'])->name('edit');
        Route::put('/{event}', [SettingsController::class, 'update'])->name('update');
        Route::delete('/{event}', [SettingsController::class, 'destroy'])->name('destroy');
        Route::patch('/{event}/toggle-status', [SettingsController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Event Packages
    Route::prefix('events/{event}/packages')->name('events.packages.')->group(function () {
        Route::get('/', [EventPackageController::class, 'index'])->name('index');
        Route::get('/create', [EventPackageController::class, 'create'])->name('create');
        Route::post('/', [EventPackageController::class, 'store'])->name('store');
        Route::get('/{package}/edit', [EventPackageController::class, 'edit'])->name('edit');
        Route::put('/{package}', [EventPackageController::class, 'update'])->name('update');
        Route::delete('/{package}', [EventPackageController::class, 'destroy'])->name('destroy');
        Route::patch('/{package}/toggle-status', [EventPackageController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Car Types
    Route::prefix('car-types')->name('car-types.')->group(function () {
        Route::get('/', [CarTypeController::class, 'index'])->name('index');
        Route::get('/create', [CarTypeController::class, 'create'])->name('create');
        Route::post('/', [CarTypeController::class, 'store'])->name('store');
        Route::get('/{carType}/edit', [CarTypeController::class, 'edit'])->name('edit');
        Route::put('/{carType}', [CarTypeController::class, 'update'])->name('update');
        Route::delete('/{carType}', [CarTypeController::class, 'destroy'])->name('destroy');
        Route::patch('/{carType}/toggle-status', [CarTypeController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Extensions
    Route::prefix('extensions')->name('extensions.')->group(function () {
        Route::get('/', [ExtensionController::class, 'index'])->name('index');
        Route::get('/create', [ExtensionController::class, 'create'])->name('create');
        Route::post('/', [ExtensionController::class, 'store'])->name('store');
        Route::get('/{extension}/edit', [ExtensionController::class, 'edit'])->name('edit');
        Route::put('/{extension}', [ExtensionController::class, 'update'])->name('update');
        Route::delete('/{extension}', [ExtensionController::class, 'destroy'])->name('destroy');
        Route::patch('/{extension}/toggle-status', [ExtensionController::class, 'toggleStatus'])->name('toggle-status');
    });
});
