<?php

namespace App\Services;

use App\Models\DataBatch;
use App\Models\RenewalData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * DataQueryService
 * 
 * This service integrates RenewalQueryService with the batch workflow system.
 * It queries data from the policy database and imports it into the local MySQL database.
 */
class DataQueryService
{
    protected RenewalQueryService $renewalQueryService;

    public function __construct(RenewalQueryService $renewalQueryService)
    {
        $this->renewalQueryService = $renewalQueryService;
    }

    /**
     * Query data from Policy DB for a specific month and create a batch
     * 
     * @param string $month Format: Y-m (e.g., 2025-01)
     * @param int $createdBy User ID
     * @param string $branch Branch filter (optional)
     * @param string $toc Type of Coverage (default: 02)
     * @return DataBatch
     */
    public function queryDataForMonth(
        string $month,
        int $createdBy,
        string $branch = '',
        string $toc = '02'
    ): DataBatch {
        $batchNumber = DataBatch::generateBatchNumber($month);

        // Create new batch with status 'processing'
        $batch = DataBatch::create([
            'batch_number' => $batchNumber,
            'month' => $month,
            'status' => 'processing',
            'created_by' => $createdBy,
            'notes' => "Querying data for {$month}...",
        ]);

        try {
            Log::info("Starting renewal query for batch {$batchNumber}", [
                'month' => $month,
                'branch' => $branch,
                'toc' => $toc,
            ]);

            // Query data from policy database
            $renewalData = $this->renewalQueryService->queryRenewalData(
                '2012-01-01',
                '2025-12-31',
                $branch,
                $toc
            );

            Log::info("Query returned {$renewalData->count()} records");

            if ($renewalData->isEmpty()) {
                $batch->update([
                    'status' => DataBatch::STATUS_PENDING_UW,
                    'total_records' => 0,
                    'notes' => 'No data found for this period.',
                ]);
                return $batch;
            }

            // Import data into local database
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            // Process in chunks for performance
            $chunks = $renewalData->chunk(500);

            foreach ($chunks as $chunk) {
                foreach ($chunk as $row) {
                    try {
                        $this->insertRenewalData($row, $batch, $month);
                        $successCount++;
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errors[] = [
                            'reference' => $row['ReferenceNo'] ?? 'unknown',
                            'error' => $e->getMessage(),
                        ];

                        if ($failedCount <= 100) { // Log first 100 errors
                            Log::warning("Failed to import record", [
                                'reference' => $row['ReferenceNo'] ?? 'unknown',
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            // Update batch status
            $batch->update([
                'status' => DataBatch::STATUS_PENDING_UW,
                'total_records' => $successCount,
                'notes' => $failedCount > 0 
                    ? "Imported {$successCount} records, {$failedCount} failed."
                    : "Successfully imported {$successCount} records.",
            ]);

            Log::info("Batch {$batchNumber} completed", [
                'success' => $successCount,
                'failed' => $failedCount,
            ]);

            return $batch;

        } catch (\Exception $e) {
            Log::error("Batch {$batchNumber} failed: " . $e->getMessage());

            $batch->update([
                'status' => 'failed',
                'notes' => 'Query failed: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Insert a single renewal data record
     */
    protected function insertRenewalData(array $row, DataBatch $batch, string $month): RenewalData
    {
        // Get No Kontrak (ReferenceNo) and validate
        $noKontrak = (string)($row['ReferenceNo'] ?? '');
        $noKontrak = preg_replace('/[\s\-]/', '', $noKontrak);

        // Determine process type based on contract length
        $processType = strlen($noKontrak) <= 13 ? 'auto' : 'manual';

        // Check for duplicate in this batch
        $exists = RenewalData::where('batch_id', $batch->id)
            ->where('no_kontrak', $noKontrak)
            ->exists();

        if ($exists) {
            throw new \Exception("Duplicate No Kontrak: {$noKontrak}");
        }

        return RenewalData::create([
            // Batch & Workflow
            'batch_id' => $batch->id,
            'batch_month' => $month,
            'workflow_status' => RenewalData::STATUS_PENDING_UW,
            'partner_status' => 'pending',
            'process_type' => $processType, // 'auto' or 'manual'

            // Identification
            'no_kontrak' => $noKontrak,
            'no_polis' => $row['Policy_No'] ?? null,
            'ano' => $row['ANO'] ?? null,

            // Dates
            'start_date' => $row['Start_Date'] ?? null,
            'end_date' => $row['End_Date'] ?? null,

            // Customer
            'nama_tertanggung' => $row['Insured_Name'] ?? null,
            'policy_holder' => $row['Policy_Holder'] ?? null,
            'no_hp' => $row['HP_Tertanggung'] ?? null,
            'telp' => $row['Telp_Tertanggung'] ?? null,
            'tanggal_lahir' => $row['Tanggal_Lahir'] ?? null,

            // Vehicle
            'no_polisi' => $row['No_Polisi'] ?? null,
            'merk_kendaraan' => $row['Merk'] ?? null,
            'model' => $row['Model'] ?? null,
            'tahun_kendaraan' => $row['Tahun_Pembuatan'] ?? null,
            'type_kendaraan' => $row['Type_Kendaraan'] ?? null,
            'jenis_ev' => $row['Jenis_EV'] ?? 'Konvensional',

            // Coverage & Premium
            'tsi' => $row['TSI100'] ?? 0,
            'premi' => $row['Premi'] ?? 0,
            'gross' => $row['Gross'] ?? 0,
            'discount' => $row['Discount'] ?? 0,
            'rate' => $row['Rate'] ?? 0,
            'rate_perluasan' => $row['Perluasan_Rate'] ?? 0,
            'coverage_rate' => $row['Coverage_Rate'] ?? null,
            'coverage_perluasan_rate' => $row['Coverage_Perluasan_Rate'] ?? null,

            // Main Coverage
            'main_cover_cmp' => $row['MAIN_COVER_CMP'] ?? 0,
            'main_cover_tlo' => $row['MAIN_COVER_TLO'] ?? 0,

            // Extensions
            'rscc' => $row['RSCC'] ?? 0,
            'eqvet' => $row['EQVET'] ?? 0,
            'tshfl' => $row['TSHFL'] ?? 0,
            'tpl' => $row['TPL'] ?? 0,
            'pa_penumpang' => $row['PA_PENUMPANG'] ?? 0,
            'pa_pengemudi' => $row['PA_PENGEMUDI'] ?? 0,
            'bengkel_authorized' => $row['BENGKEL_AUTHORIZED'] ?? 0,

            // Branch & Agent
            'branch' => $row['Branch'] ?? null,
            'nama_cabang' => $row['Nama_Cabang'] ?? null,
            'nama_marketing' => $row['Nama_Marketing'] ?? null,
            'wilayah' => $row['Wilayah'] ?? null,

            // Claim History
            'jumlah_klaim' => $row['JUMLAH_KLAIM'] ?? 0,
            'incurred_claim' => $row['Incurred_Claim'] ?? 0,
            'os_claim' => $row['OS_Claim'] ?? 0,
            'loss_ratio' => $row['Loss_Ratio'] ?? 0,
            'nilai_claim' => $row['Nilai_Claim'] ?? 0,

            // Financial
            'premium_paid' => $row['Premium_Paid'] ?? 0,
            'premium_outstanding' => $row['Premium_Outstanding'] ?? 0,
            'commission_pct' => $row['Comm_%'] ?? 0,
            'commission' => $row['Commision'] ?? 0,

            // Other
            'segment' => $row['Segment'] ?? null,
            'business_source' => $row['Business_Source'] ?? null,
            'toc' => $row['TOC'] ?? null,
            'deductible_existing' => $row['Deductible_Existing'] ?? null,
            'total_renewal' => $row['Total_Renewal'] ?? 0,
            'location' => $row['Location'] ?? null,

            // TSI Details
            'tsi_pa_passanger' => $row['TSI_PA_PASSANGER'] ?? 0,
            'tsi_pa_driver' => $row['TSI_PA_DRIVER'] ?? 0,
            'tsi_tpl' => $row['TSI_TPL'] ?? 0,

            // Tax
            'amount_vat' => $row['AMOUNT_VAT'] ?? 0,
            'amount_tax' => $row['AMOUNT_TAX'] ?? 0,

            // Store full raw data
            'raw_data' => $row,
        ]);
    }

    /**
     * Get available months for query dropdown
     */
    public function getAvailableMonths(): array
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->addMonths($i); // Future months for renewal
            $months[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        }
        return $months;
    }

    /**
     * Test database connections
     */
    public function testConnections(): array
    {
        $results = [];

        // Test Main DB (MySQL)
        try {
            DB::connection('mysql')->getPdo();
            $results['mysql'] = ['status' => 'OK', 'message' => 'Connected'];
        } catch (\Exception $e) {
            $results['mysql'] = ['status' => 'FAILED', 'message' => $e->getMessage()];
        }

        // Test Policy DB (SQL Server)
        try {
            DB::connection('policy_db')->getPdo();
            $results['policy_db'] = ['status' => 'OK', 'message' => 'Connected'];
        } catch (\Exception $e) {
            $results['policy_db'] = ['status' => 'FAILED', 'message' => $e->getMessage()];
        }

        return $results;
    }
}
