<?php

namespace App\Jobs;

use App\Models\RenewalData;
use App\Services\PartnerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendToPartnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // Retry after 60 seconds

    protected RenewalData $renewal;

    public function __construct(RenewalData $renewal)
    {
        $this->renewal = $renewal;
    }

    public function handle(PartnerService $partnerService): void
    {
        // Check if sending is allowed
        if (!$partnerService->isSendingAllowed()) {
            // Release back to queue with delay (check again in 1 hour)
            $this->release(3600);
            return;
        }

        // Skip if already successful
        if ($this->renewal->partner_status === 'success') {
            Log::info('Skipping already successful renewal', ['id' => $this->renewal->id]);
            return;
        }

        // Send to partner
        $result = $partnerService->sendToPartner($this->renewal);

        if ($result['success']) {
            $this->renewal->update([
                'partner_status' => 'success',
                'partner_request_id' => $result['request_id'],
                'partner_error_message' => null,
                'partner_error_code' => null,
                'partner_sent_at' => now(),
            ]);

            Log::info('Partner submission success', [
                'renewal_id' => $this->renewal->id,
                'request_id' => $result['request_id'],
            ]);
        } else {
            $humanMessage = PartnerService::getHumanErrorMessage(
                $result['error_code'],
                $result['error_message']
            );

            $this->renewal->update([
                'partner_status' => 'failed',
                'partner_error_code' => $result['error_code'],
                'partner_error_message' => $humanMessage,
                'partner_retry_count' => $this->renewal->partner_retry_count + 1,
            ]);

            Log::warning('Partner submission failed', [
                'renewal_id' => $this->renewal->id,
                'error_code' => $result['error_code'],
                'error_message' => $humanMessage,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->renewal->update([
            'partner_status' => 'failed',
            'partner_error_code' => 'JOB_FAILED',
            'partner_error_message' => 'Pengiriman gagal setelah beberapa percobaan: ' . $exception->getMessage(),
        ]);

        Log::error('SendToPartnerJob failed', [
            'renewal_id' => $this->renewal->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
