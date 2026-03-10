<?php

namespace App\Console\Commands;

use App\Services\RenewalQueryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class QueryRenewalData extends Command
{
    protected $signature = 'renewal:query 
                            {--month= : Month to query (format: 01-12)}
                            {--year= : Year to query (format: 2025)}
                            {--branch= : Branch code filter (optional)}
                            {--toc=02 : Type of Coverage (default: 02 for Motor)}
                            {--export : Export to Excel}';

    protected $description = 'Query renewal data from policy database';

    public function handle(RenewalQueryService $service): int
    {
        $month = $this->option('month') ?? date('m');
        $year = $this->option('year') ?? date('Y');
        $branch = $this->option('branch') ?? '';
        $toc = $this->option('toc');

        $this->info("Querying renewal data...");
        $this->info("  Month: {$month}");
        $this->info("  Year: {$year}");
        $this->info("  Branch: " . ($branch ?: 'All'));
        $this->info("  TOC: {$toc}");
        $this->newLine();

        $startTime = microtime(true);

        try {
            // Build date range (policies expiring in the selected month)
            $startDate = "{$year}-{$month}-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            $this->info("Date range: {$startDate} to {$endDate}");
            $this->newLine();

            // Query data
            $this->info("Fetching data from database...");
            $data = $service->queryRenewalData(
                '2012-01-01', // Historical start
                '2025-12-31', // End date
                $branch,
                $toc
            );

            $elapsed = round(microtime(true) - $startTime, 2);

            $this->newLine();
            $this->info("✓ Query completed in {$elapsed} seconds");
            $this->info("✓ Total records: " . $data->count());

            // Show sample data
            if ($data->isNotEmpty()) {
                $this->newLine();
                $this->info("Sample record:");
                $sample = $data->first();
                $this->table(
                    ['Field', 'Value'],
                    collect($sample)->take(20)->map(fn($v, $k) => [$k, is_null($v) ? 'NULL' : (string)$v])->toArray()
                );
            }

            // Export if requested
            if ($this->option('export') && $data->isNotEmpty()) {
                $filename = storage_path("app/renewal_data_{$year}_{$month}.csv");
                $this->exportToCsv($data, $filename);
                $this->info("✓ Exported to: {$filename}");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Query failed: " . $e->getMessage());
            Log::error("Renewal query failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    protected function exportToCsv($data, $filename): void
    {
        $handle = fopen($filename, 'w');
        
        // Header
        if ($data->isNotEmpty()) {
            fputcsv($handle, array_keys($data->first()));
        }

        // Data
        foreach ($data as $row) {
            fputcsv($handle, array_values((array)$row));
        }

        fclose($handle);
    }
}
