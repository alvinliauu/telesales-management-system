<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to add columns for the full 87-column renewal data
 * 
 * EXISTING COLUMNS (already in table):
 * - id, renewal_event_id, no_kontrak, ano, end_date
 * - nama_tertanggung, policy_holder, merk, tipe, tahun_kendaraan
 * - nilai_pertanggungan, premi_comprehensive, premi_tlo, premi_comprehensive_extended
 * - no_polis, wilayah, jumlah_klaim, tahun_renewal, jaminan_existing
 * - no_telepon, email, call_status, partner_status, workflow_status, process_type
 * - batch_id, batch_month, uw_*, marketing_*, partner_*, call_*, assigned_to, etc.
 * 
 * THIS MIGRATION ADDS: New columns from the 87-column query
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('renewal_data', function (Blueprint $table) {
            // === DATES ===
            if (!Schema::hasColumn('renewal_data', 'start_date')) {
                $table->date('start_date')->nullable();
            }

            // === CUSTOMER INFO ===
            if (!Schema::hasColumn('renewal_data', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'metode_bayar')) {
                $table->string('metode_bayar', 100)->nullable();
            }

            // === VEHICLE INFO ===
            if (!Schema::hasColumn('renewal_data', 'no_polisi')) {
                $table->string('no_polisi', 50)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'model')) {
                $table->string('model', 255)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'type_kendaraan')) {
                $table->string('type_kendaraan', 100)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'jenis_ev')) {
                $table->string('jenis_ev', 50)->default('Konvensional');
            }
            if (!Schema::hasColumn('renewal_data', 'function_kendaraan')) {
                $table->string('function_kendaraan', 100)->nullable();
            }

            // === COVERAGE & PREMIUM ===
            if (!Schema::hasColumn('renewal_data', 'tsi')) {
                $table->decimal('tsi', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'tsi_currency')) {
                $table->string('tsi_currency', 10)->default('IDR');
            }
            if (!Schema::hasColumn('renewal_data', 'tsi_currency_rate')) {
                $table->decimal('tsi_currency_rate', 10, 4)->default(1);
            }
            if (!Schema::hasColumn('renewal_data', 'premi')) {
                $table->decimal('premi', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'gross')) {
                $table->decimal('gross', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'discount')) {
                $table->decimal('discount', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'discount_pct')) {
                $table->decimal('discount_pct', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'rate')) {
                $table->decimal('rate', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'rate_perluasan')) {
                $table->decimal('rate_perluasan', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'coverage_rate')) {
                $table->string('coverage_rate', 500)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'coverage_perluasan_rate')) {
                $table->string('coverage_perluasan_rate', 500)->nullable();
            }

            // === MAIN COVERAGE FLAGS ===
            if (!Schema::hasColumn('renewal_data', 'main_cover_cmp')) {
                $table->decimal('main_cover_cmp', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'main_cover_tlo')) {
                $table->decimal('main_cover_tlo', 10, 4)->default(0);
            }

            // === EXTENSION RATES ===
            if (!Schema::hasColumn('renewal_data', 'rscc')) {
                $table->decimal('rscc', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'rsccts')) {
                $table->decimal('rsccts', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'eqvet')) {
                $table->decimal('eqvet', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'ts')) {
                $table->decimal('ts', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'tshfl')) {
                $table->decimal('tshfl', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'tpl')) {
                $table->decimal('tpl', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'tpl_penumpang')) {
                $table->decimal('tpl_penumpang', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'pa_penumpang')) {
                $table->decimal('pa_penumpang', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'pa_pengemudi')) {
                $table->decimal('pa_pengemudi', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'bengkel_authorized')) {
                $table->decimal('bengkel_authorized', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'theft')) {
                $table->decimal('theft', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'he_tlo')) {
                $table->decimal('he_tlo', 10, 4)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'he_compre')) {
                $table->decimal('he_compre', 10, 4)->default(0);
            }

            // === TSI DETAILS ===
            if (!Schema::hasColumn('renewal_data', 'tsi_pa_passanger')) {
                $table->decimal('tsi_pa_passanger', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'tsi_pa_driver')) {
                $table->decimal('tsi_pa_driver', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'tsi_tpl')) {
                $table->decimal('tsi_tpl', 18, 2)->default(0);
            }

            // === BRANCH & AGENT ===
            if (!Schema::hasColumn('renewal_data', 'branch')) {
                $table->string('branch', 10)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'nama_cabang')) {
                $table->string('nama_cabang', 255)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'nama_marketing')) {
                $table->string('nama_marketing', 255)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'pholder')) {
                $table->string('pholder', 50)->nullable();
            }

            // === CLAIM HISTORY ===
            if (!Schema::hasColumn('renewal_data', 'incurred_claim')) {
                $table->decimal('incurred_claim', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'os_claim')) {
                $table->decimal('os_claim', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'loss_ratio')) {
                $table->decimal('loss_ratio', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'nilai_claim')) {
                $table->decimal('nilai_claim', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'loss_date')) {
                $table->date('loss_date')->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'loss_place')) {
                $table->string('loss_place', 500)->nullable();
            }

            // === FINANCIAL ===
            if (!Schema::hasColumn('renewal_data', 'premium_paid')) {
                $table->decimal('premium_paid', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'premium_outstanding')) {
                $table->decimal('premium_outstanding', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'commission_pct')) {
                $table->decimal('commission_pct', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'commission')) {
                $table->decimal('commission', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'loading_premi')) {
                $table->decimal('loading_premi', 10, 4)->default(0);
            }

            // === BUSINESS INFO ===
            if (!Schema::hasColumn('renewal_data', 'segment')) {
                $table->string('segment', 50)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'business_source')) {
                $table->string('business_source', 500)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'toc')) {
                $table->string('toc', 100)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'member')) {
                $table->string('member', 10)->default('No');
            }
            if (!Schema::hasColumn('renewal_data', 'istype')) {
                $table->string('istype', 10)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'status_backup')) {
                $table->string('status_backup', 100)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'remarks')) {
                $table->text('remarks')->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'total_akumulasi')) {
                $table->decimal('total_akumulasi', 18, 2)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'total_renewal')) {
                $table->integer('total_renewal')->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'location')) {
                $table->string('location', 255)->nullable();
            }

            // === SHARE INFO ===
            if (!Schema::hasColumn('renewal_data', 'pct_share')) {
                $table->decimal('pct_share', 10, 2)->default(100);
            }
            if (!Schema::hasColumn('renewal_data', 'facultative')) {
                $table->decimal('facultative', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'fshare')) {
                $table->decimal('fshare', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'ourshare')) {
                $table->decimal('ourshare', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'bcai_share')) {
                $table->decimal('bcai_share', 10, 2)->default(100);
            }

            // === DEDUCTIBLE ===
            if (!Schema::hasColumn('renewal_data', 'deductible_existing')) {
                $table->text('deductible_existing')->nullable();
            }

            // === TAX ===
            if (!Schema::hasColumn('renewal_data', 'amount_vat')) {
                $table->decimal('amount_vat', 18, 2)->default(0);
            }
            if (!Schema::hasColumn('renewal_data', 'amount_tax')) {
                $table->decimal('amount_tax', 18, 2)->default(0);
            }

            // === REFERENCE ===
            if (!Schema::hasColumn('renewal_data', 'original_doc_no')) {
                $table->string('original_doc_no', 100)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'reference_no')) {
                $table->string('reference_no', 100)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'value_id')) {
                $table->string('value_id', 100)->nullable();
            }

            // === COLOR CLASSIFICATION ===
            if (!Schema::hasColumn('renewal_data', 'color_class')) {
                $table->string('color_class', 20)->nullable();
                // putih, hijau, krem, ungu, kuning, merah
            }
            if (!Schema::hasColumn('renewal_data', 'color_reason')) {
                $table->string('color_reason', 500)->nullable();
            }

            // === CALCULATED PROGRAMS ===
            if (!Schema::hasColumn('renewal_data', 'program_1_premium')) {
                $table->decimal('program_1_premium', 18, 2)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'program_2_premium')) {
                $table->decimal('program_2_premium', 18, 2)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'program_3_premium')) {
                $table->decimal('program_3_premium', 18, 2)->nullable();
            }
            if (!Schema::hasColumn('renewal_data', 'program_data')) {
                $table->json('program_data')->nullable();
            }

            // === RAW DATA ===
            if (!Schema::hasColumn('renewal_data', 'raw_data')) {
                $table->json('raw_data')->nullable();
            }
        });

        // Add indexes
        Schema::table('renewal_data', function (Blueprint $table) {
            if (!Schema::hasIndex('renewal_data', 'renewal_data_color_class_index')) {
                $table->index('color_class');
            }
            if (!Schema::hasIndex('renewal_data', 'renewal_data_jenis_ev_index')) {
                $table->index('jenis_ev');
            }
            if (!Schema::hasIndex('renewal_data', 'renewal_data_reference_no_index')) {
                $table->index('reference_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('renewal_data', function (Blueprint $table) {
            $columns = [
                'start_date', 'tanggal_lahir', 'metode_bayar',
                'no_polisi', 'model', 'type_kendaraan', 'jenis_ev', 'function_kendaraan',
                'tsi', 'tsi_currency', 'tsi_currency_rate', 'premi', 'gross', 'discount', 'discount_pct',
                'rate', 'rate_perluasan', 'coverage_rate', 'coverage_perluasan_rate',
                'main_cover_cmp', 'main_cover_tlo',
                'rscc', 'rsccts', 'eqvet', 'ts', 'tshfl', 'tpl', 'tpl_penumpang',
                'pa_penumpang', 'pa_pengemudi', 'bengkel_authorized', 'theft', 'he_tlo', 'he_compre',
                'tsi_pa_passanger', 'tsi_pa_driver', 'tsi_tpl',
                'branch', 'nama_cabang', 'nama_marketing', 'pholder',
                'incurred_claim', 'os_claim', 'loss_ratio', 'nilai_claim', 'loss_date', 'loss_place',
                'premium_paid', 'premium_outstanding', 'commission_pct', 'commission', 'loading_premi',
                'segment', 'business_source', 'toc', 'member', 'istype', 'status_backup',
                'remarks', 'notes', 'total_akumulasi', 'total_renewal', 'location',
                'pct_share', 'facultative', 'fshare', 'ourshare', 'bcai_share',
                'deductible_existing', 'amount_vat', 'amount_tax',
                'original_doc_no', 'reference_no', 'value_id',
                'color_class', 'color_reason',
                'program_1_premium', 'program_2_premium', 'program_3_premium', 'program_data',
                'raw_data'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('renewal_data', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};