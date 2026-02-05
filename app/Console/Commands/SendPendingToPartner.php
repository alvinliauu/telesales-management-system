<?php

namespace App\Console\Commands;

use App\Models\RenewalData;
use App\Jobs\SendToPartnerJob;
use App\Services\PartnerService;
use Illuminate\Console\Command;

class SendPendingToPartner extends Command
{
    protected $signature = 'partner:send-pending {--limit=100 : Maximum records to process}';
    protected $description = 'Queue pending renewal data to be sent to partner';

    public function handle(PartnerService $partnerService): int
    {
        // Check if sending is allowed
        if (!$partnerService->isSendingAllowed()) {
            $this->info('Sending not allowed at this time (first/last 2 days of month or 11pm-7am)');
            return Command::SUCCESS;
        }

        $limit = (int) $this->option('limit');

        // Get pending records
        $pendingRecords = RenewalData::where('partner_status', 'pending')
            ->orWhere(function ($query) {
                // Retry failed records (max 3 retries)
                $query->where('partner_status', 'failed')
                    ->where('partner_retry_count', '<', 3)
                    ->where('partner_error_code', '!=', 'DUPLICATE_POLICY'); // Don't retry duplicates
            })
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($pendingRecords->isEmpty()) {
            $this->info('No pending records to send.');
            return Command::SUCCESS;
        }

        $this->info("Queueing {$pendingRecords->count()} records to send to partner...");

        $queued = 0;
        foreach ($pendingRecords as $renewal) {
            // Mark as queued
            $renewal->update(['partner_status' => 'queued']);
            
            // Dispatch job
            SendToPartnerJob::dispatch($renewal);
            $queued++;
        }

        $this->info("Successfully queued {$queued} records.");

        return Command::SUCCESS;
    }
}
