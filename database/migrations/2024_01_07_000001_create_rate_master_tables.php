<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // 1. MASTER ZONE (Wilayah)
        // =====================================================================
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();      // Z1, Z2, Z3
            $table->string('name', 100);               // Zone 1, Zone 2, Zone 3
            $table->text('description')->nullable();   // Sumatera & Kalimantan, etc.
            $table->text('provinces')->nullable();     // List provinsi (JSON atau text)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =====================================================================
        // 2. MASTER KATEGORI HARGA KENDARAAN
        // =====================================================================
        Schema::create('vehicle_price_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();      // CAT1, CAT2, CAT3, CAT4, CAT5
            $table->string('name', 100);               // Kategori 1, Kategori 2, etc.
            $table->decimal('min_price', 18, 2);       // 0, 125000000, 200000000, etc.
            $table->decimal('max_price', 18, 2);       // 125000000, 200000000, 400000000, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =====================================================================
        // 3. MASTER JENIS KENDARAAN
        // =====================================================================
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();      // KONVENSIONAL, HYBRID, EV
            $table->string('name', 100);               // Konvensional, Hybrid, Listrik (EV)
            $table->string('default_rate_type', 20)->default('batas_bawah'); // batas_bawah / batas_atas
            $table->string('main_cover_rate_type', 20)->default('batas_bawah'); // untuk CMP/TLO
            $table->string('extension_rate_type', 20)->default('batas_bawah'); // untuk perluasan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =====================================================================
        // 4. MASTER JENIS COVERAGE / PERLUASAN
        // =====================================================================
        Schema::create('coverage_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();      // CMP, TLO, RSCC, EQVET, TSHFL, TPL, PA_DRIVER, PA_PASSENGER, BENGKEL, etc.
            $table->string('name', 100);               // Comprehensive, TLO, RSCC, etc.
            $table->enum('category', ['main', 'extension']); // main = CMP/TLO, extension = lainnya
            $table->boolean('has_tsi_options')->default(false); // true untuk TPL, PA (pilih TSI)
            $table->boolean('has_ojk_rate')->default(true);     // Apakah ada rate OJK?
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =====================================================================
        // 5. MASTER TIPE TRANSAKSI
        // =====================================================================
        Schema::create('transaction_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();      // RENEWAL, UPGRADE
            $table->string('name', 100);               // Renewal, Upgrade
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =====================================================================
        // 6. MASTER TSI OPTIONS (untuk TPL, PA Driver, PA Passenger)
        // =====================================================================
        Schema::create('tsi_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coverage_type_id')->constrained('coverage_types')->onDelete('cascade');
            $table->decimal('tsi_amount', 18, 2);      // 5000000, 10000000, 25000000, etc.
            $table->string('label', 50);               // "5 Juta", "10 Juta", etc.
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['coverage_type_id', 'tsi_amount']);
        });

        // =====================================================================
        // 7. MASTER RATE OJK (Batas Atas & Batas Bawah) - untuk CMP, TLO, Extensions %
        // =====================================================================
        Schema::create('ojk_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coverage_type_id')->constrained('coverage_types')->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete(); // null = semua zone
            $table->foreignId('vehicle_price_category_id')->nullable()->constrained('vehicle_price_categories')->nullOnDelete(); // null = semua kategori
            $table->foreignId('transaction_type_id')->nullable()->constrained('transaction_types')->nullOnDelete(); // null = semua tipe (renewal/upgrade)
            
            $table->decimal('batas_bawah', 10, 4)->nullable();     // Rate batas bawah (dalam %)
            $table->decimal('batas_atas', 10, 4)->nullable();      // Rate batas atas (dalam %)
            
            $table->year('effective_year')->default(2024);         // Tahun berlaku
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['coverage_type_id', 'zone_id', 'vehicle_price_category_id', 'effective_year'], 'ojk_rates_lookup_idx');
        });

        // =====================================================================
        // 8. MASTER COVERAGE RATES (untuk TPL, PA, Bengkel, Admin - Flat atau %)
        //    Tabel ini untuk rate default per coverage yang bisa flat atau %
        // =====================================================================
        Schema::create('coverage_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coverage_type_id')->constrained('coverage_types')->onDelete('cascade');
            $table->foreignId('vehicle_type_id')->nullable()->constrained('vehicle_types')->nullOnDelete(); // null = semua jenis kendaraan
            $table->foreignId('tsi_option_id')->nullable()->constrained('tsi_options')->nullOnDelete(); // untuk TPL, PA yang punya pilihan TSI
            $table->foreignId('transaction_type_id')->nullable()->constrained('transaction_types')->nullOnDelete(); // null = semua tipe

            // Rate bisa flat atau percentage
            $table->enum('rate_mode', ['flat', 'percentage'])->default('flat');
            $table->decimal('rate_value', 18, 4);      // Nilai rate (flat dalam Rupiah, percentage dalam %)
            $table->string('rate_type', 20)->default('default'); // default, batas_bawah, batas_atas

            // Kondisi khusus renewal
            $table->boolean('check_previous_year')->default(false); // Cek kondisi tahun sebelumnya?
            $table->string('previous_year_condition', 100)->nullable(); // e.g., "has_coverage", "rate >= 0.5"
            $table->decimal('rate_if_condition_met', 18, 4)->nullable(); // Rate jika kondisi terpenuhi
            $table->decimal('rate_if_condition_not_met', 18, 4)->nullable(); // Rate jika kondisi tidak terpenuhi

            $table->year('effective_year')->default(2024);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['coverage_type_id', 'vehicle_type_id', 'transaction_type_id'], 'coverage_rates_lookup_idx');
        });

        // =====================================================================
        // 9. RATE EVENTS (Event Promo dari Marketing)
        // =====================================================================
        Schema::create('rate_events', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();      // PROMO-EV-JUL2025, PROMO-BENGKEL-JUN2025
            $table->string('name', 255);               // Promo EV Juli 2025
            $table->text('description')->nullable();
            $table->date('start_date');                // Tanggal mulai berlaku
            $table->date('end_date');                  // Tanggal selesai
            $table->enum('status', ['draft', 'active', 'expired', 'cancelled'])->default('draft');
            $table->integer('priority')->default(0);   // Jika ada multiple event, mana yang dipakai
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['start_date', 'end_date', 'status']);
        });

        // =====================================================================
        // 10. RATE EVENT RULES (Detail Override per Event)
        // =====================================================================
        Schema::create('rate_event_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_event_id')->constrained('rate_events')->onDelete('cascade');
            
            // Filter: null = berlaku untuk semua
            $table->foreignId('vehicle_type_id')->nullable()->constrained('vehicle_types')->nullOnDelete();
            $table->foreignId('coverage_type_id')->nullable()->constrained('coverage_types')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('vehicle_price_category_id')->nullable()->constrained('vehicle_price_categories')->nullOnDelete();
            $table->foreignId('transaction_type_id')->nullable()->constrained('transaction_types')->nullOnDelete();
            $table->foreignId('tsi_option_id')->nullable()->constrained('tsi_options')->nullOnDelete(); // untuk TPL/PA

            // Override Type
            $table->enum('override_type', [
                'use_batas_bawah',      // Pakai batas bawah OJK
                'use_batas_atas',       // Pakai batas atas OJK
                'use_custom_rate',      // Pakai rate custom
                'use_flat_amount',      // Pakai flat amount
                'use_percentage',       // Pakai percentage
                'follow_previous',      // Ikut tahun sebelumnya
                'tsi_add_amount',       // TSI tahun lalu + nominal (untuk TPL)
                'no_charge',            // Gratis / Rp 0
            ]);

            // Override Values
            $table->decimal('custom_rate', 10, 4)->nullable();       // Jika use_custom_rate (dalam %)
            $table->decimal('flat_amount', 18, 2)->nullable();       // Jika use_flat_amount (dalam Rupiah)
            $table->decimal('percentage_value', 10, 4)->nullable();  // Jika use_percentage
            $table->decimal('tsi_add_amount', 18, 2)->nullable();    // Jika tsi_add_amount (e.g., +5000000)
            $table->decimal('discount_percent', 10, 2)->nullable();  // Diskon tambahan (%)

            // Kondisi khusus
            $table->boolean('only_if_previous_exists')->default(false); // Hanya jika tahun lalu ada coverage ini
            $table->string('previous_condition', 255)->nullable();      // Kondisi tambahan

            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =====================================================================
        // 11. RATE CALCULATION LOG (History perhitungan)
        // =====================================================================
        Schema::create('rate_calculation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renewal_data_id')->constrained('renewal_data')->onDelete('cascade');
            $table->foreignId('rate_event_id')->nullable()->constrained('rate_events')->nullOnDelete();
            
            $table->string('coverage_code', 20);
            $table->string('vehicle_type', 20);
            $table->string('zone_code', 10)->nullable();
            $table->string('price_category', 10)->nullable();
            $table->string('transaction_type', 20);
            
            $table->string('rate_source', 50);         // ojk_rate, coverage_rate, event_override, previous_year
            $table->string('rate_mode', 20);           // flat, percentage
            $table->decimal('rate_applied', 18, 4);    // Rate yang dipakai
            $table->decimal('tsi_used', 18, 2)->nullable(); // TSI yang dipakai untuk perhitungan
            $table->decimal('premium_calculated', 18, 2);
            
            $table->json('calculation_details')->nullable(); // Detail lengkap perhitungan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_calculation_logs');
        Schema::dropIfExists('rate_event_rules');
        Schema::dropIfExists('rate_events');
        Schema::dropIfExists('coverage_rates');
        Schema::dropIfExists('ojk_rates');
        Schema::dropIfExists('tsi_options');
        Schema::dropIfExists('transaction_types');
        Schema::dropIfExists('coverage_types');
        Schema::dropIfExists('vehicle_types');
        Schema::dropIfExists('vehicle_price_categories');
        Schema::dropIfExists('zones');
    }
};
