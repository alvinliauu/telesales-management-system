<?php

namespace App\Services;

use App\Models\DataBatch;
use App\Models\RenewalData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataQueryService
{
    /**
     * Query data from internal database for a specific month
     * 
     * TODO: Implement your actual internal database query logic here
     * This is a placeholder that you need to customize based on your database structure
     */
    public function queryDataForMonth(string $month, int $createdBy): DataBatch
    {
        $batchNumber = DataBatch::generateBatchNumber($month);
        
        // Create new batch
        $batch = DataBatch::create([
            'batch_number' => $batchNumber,
            'month' => $month,
            'status' => DataBatch::STATUS_PENDING_UW,
            'created_by' => $createdBy,
        ]);

        try {
            // TODO: Replace this with your actual internal database query
            // Example: Query from another database connection or table
            $rawData = $this->fetchFromInternalDatabase($month);

            $successCount = 0;
            $errors = [];

            foreach ($rawData as $row) {
                try {
                    // Validate and insert data
                    $this->insertRenewalData($row, $batch, $month);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $row,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Update batch counts
            $batch->update([
                'total_records' => $successCount,
            ]);

            Log::info("Data query completed for {$month}", [
                'batch_id' => $batch->id,
                'total' => $successCount,
                'errors' => count($errors),
            ]);

        } catch (\Exception $e) {
            Log::error("Data query failed for {$month}: " . $e->getMessage());
            throw $e;
        }

        return $batch;
    }

    /**
     * TODO: Implement your actual database query here
     * This is a placeholder method - replace with your actual logic
     */
    protected function fetchFromInternalDatabase(string $month): array
    {
        // Example: Query from another database connection
        // return DB::connection('internal')
        //     ->table('policies')
        //     ->whereRaw("DATE_FORMAT(expiry_date, '%Y-%m') = ?", [$month])
        //     ->get()
        //     ->toArray();

        // Example: Query from same database, different table
        // return DB::table('source_policies')
        //     ->whereRaw("DATE_FORMAT(end_date, '%Y-%m') = ?", [$month])
        //     ->get()
        //     ->toArray();

        // Placeholder: Return empty array
        // You MUST implement this method with your actual query
        return [];
    }

    /**
     * TODO: Map your internal database fields to renewal_data fields
     * This is a placeholder method - customize the mapping
     */
    protected function insertRenewalData($row, DataBatch $batch, string $month): RenewalData
    {
        // Convert object to array if needed
        $data = is_object($row) ? (array) $row : $row;

        // TODO: Map your internal database column names to the system fields
        // Example mapping (adjust based on your actual column names):
        return RenewalData::create([
            'batch_id' => $batch->id,
            'batch_month' => $month,
            'workflow_status' => RenewalData::STATUS_PENDING_UW,
            'partner_status' => 'pending',
            
            // Map your internal columns here:
            'no_kontrak' => $data['contract_number'] ?? $data['no_kontrak'] ?? null,
            'no_polis' => $data['policy_number'] ?? $data['no_polis'] ?? null,
            'nama_tertanggung' => $data['insured_name'] ?? $data['nama_tertanggung'] ?? null,
            'no_hp' => $data['phone'] ?? $data['no_hp'] ?? null,
            'email' => $data['email'] ?? null,
            'ano' => $data['plate_number'] ?? $data['ano'] ?? null,
            'merk_kendaraan' => $data['vehicle_brand'] ?? $data['merk_kendaraan'] ?? null,
            'tahun_kendaraan' => $data['vehicle_year'] ?? $data['tahun_kendaraan'] ?? null,
            'jenis_coverage' => $data['coverage_type'] ?? $data['jenis_coverage'] ?? null,
            'tsi' => $data['sum_insured'] ?? $data['tsi'] ?? 0,
            'premi' => $data['premium'] ?? $data['premi'] ?? 0,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? $data['expiry_date'] ?? null,
            'agent' => $data['agent'] ?? null,
            'cabang' => $data['branch'] ?? $data['cabang'] ?? null,
            'raw_data' => $data,
        ]);
    }

    /**
     * Get available months for querying
     * TODO: Implement based on your internal database
     */
    public function getAvailableMonths(): array
    {
        // Return last 12 months as options
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months[] = [
                'value' => $date->format('Y-m'),
                'label' => $date->format('F Y'),
            ];
        }
        return $months;
    }
}
