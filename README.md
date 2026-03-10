# Rate Master + Rate Events UI
# Update untuk Insurance Management System

## Isi Package:

```
app/
├── Models/
│   ├── Zone.php
│   ├── VehicleType.php
│   ├── VehiclePriceCategory.php
│   ├── CoverageType.php
│   ├── TransactionType.php
│   ├── TsiOption.php
│   ├── RateEvent.php
│   └── RateEventRule.php
└── Http/Controllers/
    └── RateEventController.php

database/
├── migrations/
│   └── 2024_01_07_000001_create_rate_master_tables.php
└── seeders/
    └── RateMasterSeeder.php

resources/views/rate-events/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php

routes/
└── rate-events.php (perlu di-merge manual ke web.php)
```

## Cara Install:

```bash
cd ~/Documents/insurance-management-system

# 1. Extract langsung ke folder project
unzip -o ~/Downloads/rate-master-update.zip

# 2. Tambahkan routes ke routes/web.php
#    Buka routes/web.php, lalu tambahkan isi dari routes/rate-events.php
#    di dalam middleware auth group

# 3. Run migration (jika belum)
php artisan migrate

# 4. Run seeder (jika belum)
php artisan db:seed --class=RateMasterSeeder
```

## Menambahkan Routes:

Buka `routes/web.php` dan tambahkan di dalam middleware auth:

```php
use App\Http\Controllers\RateEventController;

Route::middleware(['auth'])->group(function () {
    // ... routes lainnya ...

    // Rate Events
    Route::prefix('rate-events')->name('rate-events.')->group(function () {
        Route::get('/', [RateEventController::class, 'index'])->name('index');
        Route::get('/create', [RateEventController::class, 'create'])->name('create');
        Route::post('/', [RateEventController::class, 'store'])->name('store');
        Route::get('/{rateEvent}', [RateEventController::class, 'show'])->name('show');
        Route::get('/{rateEvent}/edit', [RateEventController::class, 'edit'])->name('edit');
        Route::put('/{rateEvent}', [RateEventController::class, 'update'])->name('update');
        Route::delete('/{rateEvent}', [RateEventController::class, 'destroy'])->name('destroy');
        
        Route::post('/{rateEvent}/activate', [RateEventController::class, 'activate'])->name('activate');
        Route::post('/{rateEvent}/cancel', [RateEventController::class, 'cancel'])->name('cancel');
        Route::post('/{rateEvent}/duplicate', [RateEventController::class, 'duplicate'])->name('duplicate');
        
        Route::post('/{rateEvent}/rules', [RateEventController::class, 'addRule'])->name('add-rule');
        Route::put('/{rateEvent}/rules/{rule}', [RateEventController::class, 'updateRule'])->name('update-rule');
        Route::delete('/{rateEvent}/rules/{rule}', [RateEventController::class, 'deleteRule'])->name('delete-rule');
    });
});
```

## Akses:

- URL: /rate-events
- Semua role bisa lihat
- Marketing & Admin bisa edit
