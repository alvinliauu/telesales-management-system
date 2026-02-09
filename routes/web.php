<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataQueryController;
use App\Http\Controllers\UwReviewController;
use App\Http\Controllers\MarketingReviewController;
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
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    // ============================================
    // UNDERWRITER ROUTES
    // ============================================
    
    // Data Query (UW only)
    Route::prefix('data-query')->name('data-query.')->group(function () {
        Route::get('/', [DataQueryController::class, 'index'])->name('index');
        Route::post('/query', [DataQueryController::class, 'query'])->name('query');
        Route::get('/{batch}', [DataQueryController::class, 'show'])->name('show');
        Route::delete('/{batch}', [DataQueryController::class, 'destroy'])->name('destroy');
    });

    // UW Review (UW only)
    Route::prefix('uw-review')->name('uw-review.')->group(function () {
        Route::get('/', [UwReviewController::class, 'index'])->name('index');
        Route::get('/{batch}', [UwReviewController::class, 'show'])->name('show');
        Route::get('/{batch}/download', [UwReviewController::class, 'download'])->name('download');
        Route::post('/{batch}/upload', [UwReviewController::class, 'upload'])->name('upload');
        Route::post('/{batch}/approve', [UwReviewController::class, 'approveBatch'])->name('approve-batch');
        Route::post('/{batch}/reject', [UwReviewController::class, 'rejectBatch'])->name('reject-batch');
        Route::post('/record/{record}/approve', [UwReviewController::class, 'approveRecord'])->name('approve-record');
        Route::post('/record/{record}/reject', [UwReviewController::class, 'rejectRecord'])->name('reject-record');
        Route::post('/bulk-approve', [UwReviewController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [UwReviewController::class, 'bulkReject'])->name('bulk-reject');
    });

    // ============================================
    // MARKETING ROUTES
    // ============================================
    
    // Marketing Review
    Route::prefix('marketing-review')->name('marketing-review.')->group(function () {
        Route::get('/', [MarketingReviewController::class, 'index'])->name('index');
        Route::get('/{batch}', [MarketingReviewController::class, 'show'])->name('show');
        Route::get('/{batch}/download', [MarketingReviewController::class, 'download'])->name('download');
        Route::post('/{batch}/approve', [MarketingReviewController::class, 'approveBatch'])->name('approve-batch');
        Route::post('/{batch}/return', [MarketingReviewController::class, 'returnToUw'])->name('return-to-uw');
    });

    // ============================================
    // LEGACY ROUTES (Keep for backward compatibility)
    // ============================================
    
    // Telesales - Renewal (Legacy upload)
    Route::prefix('telesales/renewal')->name('telesales.renewal.')->group(function () {
        Route::get('/', [RenewalController::class, 'index'])->name('index');
        Route::get('/upload', [RenewalController::class, 'showUpload'])->name('upload');
        Route::post('/upload', [RenewalController::class, 'upload'])->name('upload.process');
        Route::get('/{renewal}', [RenewalController::class, 'show'])->name('show');
    });

    // Telesales - Upgrade (placeholder)
    Route::get('/telesales/upgrade', function () {
        return view('telesales.upgrade.index');
    })->name('telesales.upgrade.index');

    // ============================================
    // TELESALES EVENT SETUP (Both roles)
    // ============================================
    
    // Events
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
