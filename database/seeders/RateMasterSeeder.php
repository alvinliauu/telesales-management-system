<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RateMasterSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. ZONES (Wilayah)
        // =====================================================================
        $zones = [
            ['code' => 'Z1', 'name' => 'Zone 1', 'description' => 'Sumatera dan Kepulauan Riau'],
            ['code' => 'Z2', 'name' => 'Zone 2', 'description' => 'DKI Jakarta, Jawa Barat, dan Banten'],
            ['code' => 'Z3', 'name' => 'Zone 3', 'description' => 'Wilayah lainnya selain Zone 1 dan Zone 2'],
        ];

        foreach ($zones as $zone) {
            DB::table('zones')->updateOrInsert(
                ['code' => $zone['code']],
                array_merge($zone, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // =====================================================================
        // 2. VEHICLE PRICE CATEGORIES (Kategori Harga Kendaraan)
        // =====================================================================
        $categories = [
            ['code' => 'CAT1', 'name' => 'Kategori 1', 'min_price' => 0, 'max_price' => 125000000],
            ['code' => 'CAT2', 'name' => 'Kategori 2', 'min_price' => 125000001, 'max_price' => 200000000],
            ['code' => 'CAT3', 'name' => 'Kategori 3', 'min_price' => 200000001, 'max_price' => 400000000],
            ['code' => 'CAT4', 'name' => 'Kategori 4', 'min_price' => 400000001, 'max_price' => 800000000],
            ['code' => 'CAT5', 'name' => 'Kategori 5', 'min_price' => 800000001, 'max_price' => 99999999999],
        ];

        foreach ($categories as $cat) {
            DB::table('vehicle_price_categories')->updateOrInsert(
                ['code' => $cat['code']],
                array_merge($cat, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // =====================================================================
        // 3. VEHICLE TYPES (Jenis Kendaraan)
        // =====================================================================
        $vehicleTypes = [
            [
                'code' => 'KONVENSIONAL',
                'name' => 'Konvensional',
                'default_rate_type' => 'batas_bawah',
                'main_cover_rate_type' => 'batas_bawah',
                'extension_rate_type' => 'batas_bawah',
            ],
            [
                'code' => 'HYBRID',
                'name' => 'Hybrid',
                'default_rate_type' => 'batas_bawah',
                'main_cover_rate_type' => 'batas_bawah',
                'extension_rate_type' => 'batas_bawah',
            ],
            [
                'code' => 'EV',
                'name' => 'Listrik (EV)',
                'default_rate_type' => 'batas_atas',
                'main_cover_rate_type' => 'batas_atas',
                'extension_rate_type' => 'batas_bawah',
            ],
        ];

        foreach ($vehicleTypes as $vt) {
            DB::table('vehicle_types')->updateOrInsert(
                ['code' => $vt['code']],
                array_merge($vt, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // =====================================================================
        // 4. TRANSACTION TYPES (Tipe Transaksi)
        // =====================================================================
        $transactionTypes = [
            ['code' => 'RENEWAL', 'name' => 'Renewal', 'description' => 'Perpanjangan polis dengan coverage sama atau lebih rendah'],
            ['code' => 'UPGRADE', 'name' => 'Upgrade', 'description' => 'Perpanjangan polis dengan coverage lebih tinggi (e.g., TLO ke Comprehensive)'],
        ];

        foreach ($transactionTypes as $tt) {
            DB::table('transaction_types')->updateOrInsert(
                ['code' => $tt['code']],
                array_merge($tt, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // =====================================================================
        // 5. COVERAGE TYPES (Jenis Coverage/Perluasan)
        // =====================================================================
        $coverageTypes = [
            // Main Coverage
            ['code' => 'CMP', 'name' => 'Comprehensive (All Risk)', 'category' => 'main', 'has_tsi_options' => false, 'has_ojk_rate' => true, 'sort_order' => 1],
            ['code' => 'TLO', 'name' => 'Total Loss Only', 'category' => 'main', 'has_tsi_options' => false, 'has_ojk_rate' => true, 'sort_order' => 2],
            
            // Extensions dengan OJK Rate (percentage)
            ['code' => 'RSCC', 'name' => 'RSCC (Riot, Strike, Civil Commotion)', 'category' => 'extension', 'has_tsi_options' => false, 'has_ojk_rate' => true, 'sort_order' => 10],
            ['code' => 'RSCCTS', 'name' => 'RSCC + TS', 'category' => 'extension', 'has_tsi_options' => false, 'has_ojk_rate' => true, 'sort_order' => 11],
            ['code' => 'EQVET', 'name' => 'Earthquake, Volcanic Eruption, Tsunami', 'category' => 'extension', 'has_tsi_options' => false, 'has_ojk_rate' => true, 'sort_order' => 12],
            ['code' => 'TS', 'name' => 'Terrorism & Sabotage', 'category' => 'extension', 'has_tsi_options' => false, 'has_ojk_rate' => true, 'sort_order' => 13],
            ['code' => 'TSHFL', 'name' => 'TS + Huru-Hara + Flood', 'category' => 'extension', 'has_tsi_options' => false, 'has_ojk_rate' => true, 'sort_order' => 14],
            
            // Extensions dengan TSI Options (TPL, PA)
            ['code' => 'TPL', 'name' => 'Third Party Liability (TJH)', 'category' => 'extension', 'has_tsi_options' => true, 'has_ojk_rate' => false, 'sort_order' => 20],
            ['code' => 'PA_DRIVER', 'name' => 'Personal Accident - Driver (PAD)', 'category' => 'extension', 'has_tsi_options' => true, 'has_ojk_rate' => false, 'sort_order' => 21],
            ['code' => 'PA_PASSENGER', 'name' => 'Personal Accident - Passenger (PAP)', 'category' => 'extension', 'has_tsi_options' => true, 'has_ojk_rate' => false, 'sort_order' => 22],
            
            // Extensions Flat/Percentage (Bengkel, Admin)
            ['code' => 'BENGKEL', 'name' => 'Bengkel Authorized / Rekanan', 'category' => 'extension', 'has_tsi_options' => false, 'has_ojk_rate' => false, 'sort_order' => 30],
            ['code' => 'ADMIN', 'name' => 'Biaya Administrasi Polis', 'category' => 'extension', 'has_tsi_options' => false, 'has_ojk_rate' => false, 'sort_order' => 99],
        ];

        foreach ($coverageTypes as $ct) {
            DB::table('coverage_types')->updateOrInsert(
                ['code' => $ct['code']],
                array_merge($ct, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Get IDs for foreign keys
        $zoneIds = DB::table('zones')->pluck('id', 'code');
        $categoryIds = DB::table('vehicle_price_categories')->pluck('id', 'code');
        $vehicleTypeIds = DB::table('vehicle_types')->pluck('id', 'code');
        $coverageIds = DB::table('coverage_types')->pluck('id', 'code');
        $transactionTypeIds = DB::table('transaction_types')->pluck('id', 'code');

        // =====================================================================
        // 6. TSI OPTIONS (untuk TPL, PA Driver, PA Passenger)
        // =====================================================================
        $tsiOptions = [
            // TPL
            ['coverage' => 'TPL', 'tsi_amount' => 5000000, 'label' => '5 Juta', 'sort_order' => 1],
            ['coverage' => 'TPL', 'tsi_amount' => 10000000, 'label' => '10 Juta', 'sort_order' => 2],
            ['coverage' => 'TPL', 'tsi_amount' => 25000000, 'label' => '25 Juta', 'sort_order' => 3],
            ['coverage' => 'TPL', 'tsi_amount' => 50000000, 'label' => '50 Juta', 'sort_order' => 4],
            ['coverage' => 'TPL', 'tsi_amount' => 75000000, 'label' => '75 Juta', 'sort_order' => 5],
            ['coverage' => 'TPL', 'tsi_amount' => 100000000, 'label' => '100 Juta', 'sort_order' => 6],
            
            // PA Driver
            ['coverage' => 'PA_DRIVER', 'tsi_amount' => 5000000, 'label' => '5 Juta', 'sort_order' => 1],
            ['coverage' => 'PA_DRIVER', 'tsi_amount' => 10000000, 'label' => '10 Juta', 'sort_order' => 2],
            ['coverage' => 'PA_DRIVER', 'tsi_amount' => 25000000, 'label' => '25 Juta', 'sort_order' => 3],
            ['coverage' => 'PA_DRIVER', 'tsi_amount' => 50000000, 'label' => '50 Juta', 'sort_order' => 4],
            
            // PA Passenger
            ['coverage' => 'PA_PASSENGER', 'tsi_amount' => 5000000, 'label' => '5 Juta', 'sort_order' => 1],
            ['coverage' => 'PA_PASSENGER', 'tsi_amount' => 10000000, 'label' => '10 Juta', 'sort_order' => 2],
            ['coverage' => 'PA_PASSENGER', 'tsi_amount' => 25000000, 'label' => '25 Juta', 'sort_order' => 3],
            ['coverage' => 'PA_PASSENGER', 'tsi_amount' => 50000000, 'label' => '50 Juta', 'sort_order' => 4],
        ];

        foreach ($tsiOptions as $opt) {
            DB::table('tsi_options')->updateOrInsert(
                [
                    'coverage_type_id' => $coverageIds[$opt['coverage']],
                    'tsi_amount' => $opt['tsi_amount'],
                ],
                [
                    'label' => $opt['label'],
                    'sort_order' => $opt['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Get TSI Option IDs
        $tsiOptionIds = DB::table('tsi_options')
            ->join('coverage_types', 'tsi_options.coverage_type_id', '=', 'coverage_types.id')
            ->select('tsi_options.id', 'coverage_types.code as coverage_code', 'tsi_options.tsi_amount')
            ->get()
            ->mapWithKeys(fn($row) => ["{$row->coverage_code}_{$row->tsi_amount}" => $row->id]);

        // =====================================================================
        // 7. OJK RATES - COMPREHENSIVE
        // =====================================================================
        $comprehensiveRates = [
            // Zone 1
            ['zone' => 'Z1', 'cat' => 'CAT1', 'batas_bawah' => 3.26, 'batas_atas' => 3.59],
            ['zone' => 'Z1', 'cat' => 'CAT2', 'batas_bawah' => 2.47, 'batas_atas' => 2.72],
            ['zone' => 'Z1', 'cat' => 'CAT3', 'batas_bawah' => 2.07, 'batas_atas' => 2.28],
            ['zone' => 'Z1', 'cat' => 'CAT4', 'batas_bawah' => 1.20, 'batas_atas' => 1.32],
            ['zone' => 'Z1', 'cat' => 'CAT5', 'batas_bawah' => 1.05, 'batas_atas' => 1.16],
            // Zone 2
            ['zone' => 'Z2', 'cat' => 'CAT1', 'batas_bawah' => 3.47, 'batas_atas' => 3.82],
            ['zone' => 'Z2', 'cat' => 'CAT2', 'batas_bawah' => 2.69, 'batas_atas' => 2.96],
            ['zone' => 'Z2', 'cat' => 'CAT3', 'batas_bawah' => 2.18, 'batas_atas' => 2.40],
            ['zone' => 'Z2', 'cat' => 'CAT4', 'batas_bawah' => 1.20, 'batas_atas' => 1.32],
            ['zone' => 'Z2', 'cat' => 'CAT5', 'batas_bawah' => 1.05, 'batas_atas' => 1.16],
            // Zone 3
            ['zone' => 'Z3', 'cat' => 'CAT1', 'batas_bawah' => 2.53, 'batas_atas' => 2.78],
            ['zone' => 'Z3', 'cat' => 'CAT2', 'batas_bawah' => 2.07, 'batas_atas' => 2.28],
            ['zone' => 'Z3', 'cat' => 'CAT3', 'batas_bawah' => 1.79, 'batas_atas' => 1.97],
            ['zone' => 'Z3', 'cat' => 'CAT4', 'batas_bawah' => 1.14, 'batas_atas' => 1.25],
            ['zone' => 'Z3', 'cat' => 'CAT5', 'batas_bawah' => 1.05, 'batas_atas' => 1.16],
        ];

        foreach ($comprehensiveRates as $rate) {
            DB::table('ojk_rates')->updateOrInsert(
                [
                    'coverage_type_id' => $coverageIds['CMP'],
                    'zone_id' => $zoneIds[$rate['zone']],
                    'vehicle_price_category_id' => $categoryIds[$rate['cat']],
                    'effective_year' => 2024,
                ],
                [
                    'batas_bawah' => $rate['batas_bawah'],
                    'batas_atas' => $rate['batas_atas'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // =====================================================================
        // 8. OJK RATES - TLO
        // =====================================================================
        $tloRates = [
            // Zone 1
            ['zone' => 'Z1', 'cat' => 'CAT1', 'batas_bawah' => 0.65, 'batas_atas' => 0.78],
            ['zone' => 'Z1', 'cat' => 'CAT2', 'batas_bawah' => 0.44, 'batas_atas' => 0.53],
            ['zone' => 'Z1', 'cat' => 'CAT3', 'batas_bawah' => 0.38, 'batas_atas' => 0.46],
            ['zone' => 'Z1', 'cat' => 'CAT4', 'batas_bawah' => 0.25, 'batas_atas' => 0.30],
            ['zone' => 'Z1', 'cat' => 'CAT5', 'batas_bawah' => 0.20, 'batas_atas' => 0.24],
            // Zone 2
            ['zone' => 'Z2', 'cat' => 'CAT1', 'batas_bawah' => 0.67, 'batas_atas' => 0.75],
            ['zone' => 'Z2', 'cat' => 'CAT2', 'batas_bawah' => 0.44, 'batas_atas' => 0.48],
            ['zone' => 'Z2', 'cat' => 'CAT3', 'batas_bawah' => 0.38, 'batas_atas' => 0.42],
            ['zone' => 'Z2', 'cat' => 'CAT4', 'batas_bawah' => 0.25, 'batas_atas' => 0.27],
            ['zone' => 'Z2', 'cat' => 'CAT5', 'batas_bawah' => 0.20, 'batas_atas' => 0.22],
            // Zone 3
            ['zone' => 'Z3', 'cat' => 'CAT1', 'batas_bawah' => 0.51, 'batas_atas' => 0.56],
            ['zone' => 'Z3', 'cat' => 'CAT2', 'batas_bawah' => 0.35, 'batas_atas' => 0.39],
            ['zone' => 'Z3', 'cat' => 'CAT3', 'batas_bawah' => 0.29, 'batas_atas' => 0.32],
            ['zone' => 'Z3', 'cat' => 'CAT4', 'batas_bawah' => 0.23, 'batas_atas' => 0.25],
            ['zone' => 'Z3', 'cat' => 'CAT5', 'batas_bawah' => 0.20, 'batas_atas' => 0.22],
        ];

        foreach ($tloRates as $rate) {
            DB::table('ojk_rates')->updateOrInsert(
                [
                    'coverage_type_id' => $coverageIds['TLO'],
                    'zone_id' => $zoneIds[$rate['zone']],
                    'vehicle_price_category_id' => $categoryIds[$rate['cat']],
                    'effective_year' => 2024,
                ],
                [
                    'batas_bawah' => $rate['batas_bawah'],
                    'batas_atas' => $rate['batas_atas'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // =====================================================================
        // 9. OJK RATES - EXTENSIONS (sama untuk semua zone & kategori)
        // =====================================================================
        $extensionRates = [
            ['code' => 'RSCC', 'batas_bawah' => 0.05, 'batas_atas' => 0.10],
            ['code' => 'RSCCTS', 'batas_bawah' => 0.075, 'batas_atas' => 0.15],
            ['code' => 'EQVET', 'batas_bawah' => 0.075, 'batas_atas' => 0.12],
            ['code' => 'TS', 'batas_bawah' => 0.025, 'batas_atas' => 0.05],
            ['code' => 'TSHFL', 'batas_bawah' => 0.10, 'batas_atas' => 0.15],
        ];

        foreach ($extensionRates as $ext) {
            // Insert tanpa zone & kategori (berlaku untuk semua)
            DB::table('ojk_rates')->updateOrInsert(
                [
                    'coverage_type_id' => $coverageIds[$ext['code']],
                    'zone_id' => null,
                    'vehicle_price_category_id' => null,
                    'effective_year' => 2024,
                ],
                [
                    'batas_bawah' => $ext['batas_bawah'],
                    'batas_atas' => $ext['batas_atas'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // =====================================================================
        // 10. COVERAGE RATES - TPL (Flat per TSI Option)
        // =====================================================================
        $tplRates = [
            ['tsi' => 5000000, 'rate' => 35000],
            ['tsi' => 10000000, 'rate' => 50000],
            ['tsi' => 25000000, 'rate' => 100000],
            ['tsi' => 50000000, 'rate' => 150000],
            ['tsi' => 75000000, 'rate' => 200000],
            ['tsi' => 100000000, 'rate' => 250000],
        ];

        foreach ($tplRates as $rate) {
            $tsiKey = "TPL_{$rate['tsi']}";
            if (isset($tsiOptionIds[$tsiKey])) {
                DB::table('coverage_rates')->updateOrInsert(
                    [
                        'coverage_type_id' => $coverageIds['TPL'],
                        'tsi_option_id' => $tsiOptionIds[$tsiKey],
                        'vehicle_type_id' => null, // semua jenis kendaraan
                        'effective_year' => 2024,
                    ],
                    [
                        'rate_mode' => 'flat',
                        'rate_value' => $rate['rate'],
                        'rate_type' => 'default',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // =====================================================================
        // 11. COVERAGE RATES - PA DRIVER (Flat per TSI Option)
        // =====================================================================
        $paDriverRates = [
            ['tsi' => 5000000, 'rate' => 5000],
            ['tsi' => 10000000, 'rate' => 10000],
            ['tsi' => 25000000, 'rate' => 20000],
            ['tsi' => 50000000, 'rate' => 35000],
        ];

        foreach ($paDriverRates as $rate) {
            $tsiKey = "PA_DRIVER_{$rate['tsi']}";
            if (isset($tsiOptionIds[$tsiKey])) {
                DB::table('coverage_rates')->updateOrInsert(
                    [
                        'coverage_type_id' => $coverageIds['PA_DRIVER'],
                        'tsi_option_id' => $tsiOptionIds[$tsiKey],
                        'vehicle_type_id' => null,
                        'effective_year' => 2024,
                    ],
                    [
                        'rate_mode' => 'flat',
                        'rate_value' => $rate['rate'],
                        'rate_type' => 'default',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // =====================================================================
        // 12. COVERAGE RATES - PA PASSENGER (Flat per TSI Option)
        // =====================================================================
        $paPassengerRates = [
            ['tsi' => 5000000, 'rate' => 5000],
            ['tsi' => 10000000, 'rate' => 10000],
            ['tsi' => 25000000, 'rate' => 20000],
            ['tsi' => 50000000, 'rate' => 35000],
        ];

        foreach ($paPassengerRates as $rate) {
            $tsiKey = "PA_PASSENGER_{$rate['tsi']}";
            if (isset($tsiOptionIds[$tsiKey])) {
                DB::table('coverage_rates')->updateOrInsert(
                    [
                        'coverage_type_id' => $coverageIds['PA_PASSENGER'],
                        'tsi_option_id' => $tsiOptionIds[$tsiKey],
                        'vehicle_type_id' => null,
                        'effective_year' => 2024,
                    ],
                    [
                        'rate_mode' => 'flat',
                        'rate_value' => $rate['rate'],
                        'rate_type' => 'default',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // =====================================================================
        // 13. COVERAGE RATES - BENGKEL (berbeda per jenis kendaraan)
        // =====================================================================
        
        // Konvensional: Flat Rp 50.000
        DB::table('coverage_rates')->updateOrInsert(
            [
                'coverage_type_id' => $coverageIds['BENGKEL'],
                'vehicle_type_id' => $vehicleTypeIds['KONVENSIONAL'],
                'effective_year' => 2024,
            ],
            [
                'rate_mode' => 'flat',
                'rate_value' => 50000,
                'rate_type' => 'default',
                'check_previous_year' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Hybrid: Flat Rp 50.000
        DB::table('coverage_rates')->updateOrInsert(
            [
                'coverage_type_id' => $coverageIds['BENGKEL'],
                'vehicle_type_id' => $vehicleTypeIds['HYBRID'],
                'effective_year' => 2024,
            ],
            [
                'rate_mode' => 'flat',
                'rate_value' => 50000,
                'rate_type' => 'default',
                'check_previous_year' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // EV: Percentage dengan kondisi renewal
        // - Jika tahun lalu ada bengkel 0.5% → pakai 0.5%
        // - Jika tahun lalu tidak ada → dapat 1%
        DB::table('coverage_rates')->updateOrInsert(
            [
                'coverage_type_id' => $coverageIds['BENGKEL'],
                'vehicle_type_id' => $vehicleTypeIds['EV'],
                'transaction_type_id' => $transactionTypeIds['RENEWAL'],
                'effective_year' => 2024,
            ],
            [
                'rate_mode' => 'percentage',
                'rate_value' => 1.0, // Default 1%
                'rate_type' => 'default',
                'check_previous_year' => true,
                'previous_year_condition' => 'has_bengkel_coverage',
                'rate_if_condition_met' => 0.5,      // Jika tahun lalu ada → 0.5%
                'rate_if_condition_not_met' => 1.0,  // Jika tahun lalu tidak ada → 1%
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // =====================================================================
        // 14. COVERAGE RATES - ADMIN (Flat untuk semua)
        // =====================================================================
        DB::table('coverage_rates')->updateOrInsert(
            [
                'coverage_type_id' => $coverageIds['ADMIN'],
                'vehicle_type_id' => null, // semua jenis kendaraan
                'effective_year' => 2024,
            ],
            [
                'rate_mode' => 'flat',
                'rate_value' => 50000,
                'rate_type' => 'default',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // =====================================================================
        // SUMMARY
        // =====================================================================
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('    RATE MASTER SEEDER COMPLETED!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Data yang berhasil di-seed:');
        $this->command->info('  - Zones: ' . DB::table('zones')->count());
        $this->command->info('  - Vehicle Price Categories: ' . DB::table('vehicle_price_categories')->count());
        $this->command->info('  - Vehicle Types: ' . DB::table('vehicle_types')->count());
        $this->command->info('  - Transaction Types: ' . DB::table('transaction_types')->count());
        $this->command->info('  - Coverage Types: ' . DB::table('coverage_types')->count());
        $this->command->info('  - TSI Options: ' . DB::table('tsi_options')->count());
        $this->command->info('  - OJK Rates: ' . DB::table('ojk_rates')->count());
        $this->command->info('  - Coverage Rates: ' . DB::table('coverage_rates')->count());
        $this->command->info('');
    }
}
