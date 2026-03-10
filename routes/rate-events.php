<?php

// Add these routes to your routes/web.php file
// Place them inside the auth middleware group

use App\Http\Controllers\RateEventController;

// Rate Events
Route::prefix('rate-events')->name('rate-events.')->middleware(['auth'])->group(function () {
    Route::get('/', [RateEventController::class, 'index'])->name('index');
    Route::get('/create', [RateEventController::class, 'create'])->name('create');
    Route::post('/', [RateEventController::class, 'store'])->name('store');
    Route::get('/{rateEvent}', [RateEventController::class, 'show'])->name('show');
    Route::get('/{rateEvent}/edit', [RateEventController::class, 'edit'])->name('edit');
    Route::put('/{rateEvent}', [RateEventController::class, 'update'])->name('update');
    Route::delete('/{rateEvent}', [RateEventController::class, 'destroy'])->name('destroy');
    
    // Actions
    Route::post('/{rateEvent}/activate', [RateEventController::class, 'activate'])->name('activate');
    Route::post('/{rateEvent}/cancel', [RateEventController::class, 'cancel'])->name('cancel');
    Route::post('/{rateEvent}/duplicate', [RateEventController::class, 'duplicate'])->name('duplicate');
    
    // Rules
    Route::post('/{rateEvent}/rules', [RateEventController::class, 'addRule'])->name('add-rule');
    Route::put('/{rateEvent}/rules/{rule}', [RateEventController::class, 'updateRule'])->name('update-rule');
    Route::delete('/{rateEvent}/rules/{rule}', [RateEventController::class, 'deleteRule'])->name('delete-rule');
});
