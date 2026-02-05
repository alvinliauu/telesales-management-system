<?php

namespace App\Services;

use App\Models\RenewalData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PartnerService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.partner.base_url', 'https://partner-api.example.com');
        $this->apiKey = config('services.partner.api_key', '');
    }

    /**
     * Check if sending is allowed based on time rules
     * - Not in first 2 days or last 2 days of month
     * - Not between 11pm to 7am
     */
    public function isSendingAllowed(): bool
    {
        $now = now();
        $day = (int) $now->format('d');
        $lastDay = (int) $now->endOfMonth()->format('d');
        $hour = (int) $now->format('H');

        // Check first 2 days
        if ($day <= 2) {
            Log::info('Partner sending blocked: First 2 days of month');
            return false;
        }

        // Check last 2 days
        if ($day >= ($lastDay - 1)) {
            Log::info('Partner sending blocked: Last 2 days of month');
            return false;
        }

        // Check time (11pm = 23, 7am = 7)
        // Blocked: 23, 0, 1, 2, 3, 4, 5, 6
        if ($hour >= 23 || $hour < 7) {
            Log::info('Partner sending blocked: Outside allowed hours (11pm-7am)');
            return false;
        }

        return true;
    }

    /**
     * Send renewal data to partner
     */
    public function sendToPartner(RenewalData $renewal): array
    {
        try {
            // Prepare payload
            $payload = [
                'policy_number' => $renewal->no_polis,
                'contract_number' => $renewal->no_kontrak,
                'insured_name' => $renewal->nama_tertanggung,
                'phone' => $renewal->no_hp,
                'vehicle_plate' => $renewal->ano,
                'vehicle_brand' => $renewal->merk_kendaraan,
                'vehicle_year' => $renewal->tahun_kendaraan,
                'coverage_type' => $renewal->jenis_coverage,
                'sum_insured' => $renewal->tsi,
                'premium' => $renewal->premi,
                'end_date' => $renewal->end_date?->format('Y-m-d'),
                'reference_id' => $renewal->id,
            ];

            // TODO: Replace with actual partner API call
            // $response = Http::withHeaders([
            //     'Authorization' => 'Bearer ' . $this->apiKey,
            //     'Content-Type' => 'application/json',
            // ])->post($this->baseUrl . '/api/renewal', $payload);

            // Simulate API response for now
            $response = $this->simulateApiCall($payload);

            if ($response['success']) {
                return [
                    'success' => true,
                    'request_id' => $response['request_id'],
                    'message' => $response['message'] ?? 'Success',
                ];
            } else {
                return [
                    'success' => false,
                    'error_code' => $response['error_code'] ?? 'UNKNOWN',
                    'error_message' => $response['error_message'] ?? 'Unknown error',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Partner API error: ' . $e->getMessage(), [
                'renewal_id' => $renewal->id,
            ]);

            return [
                'success' => false,
                'error_code' => 'EXCEPTION',
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Simulate API call for testing
     * Remove this when integrating with real API
     */
    private function simulateApiCall(array $payload): array
    {
        // Simulate 90% success rate
        if (rand(1, 100) <= 90) {
            return [
                'success' => true,
                'request_id' => 'REQ-' . strtoupper(uniqid()),
                'message' => 'Data received successfully',
            ];
        }

        // Simulate various errors
        $errors = [
            ['error_code' => 'INVALID_PHONE', 'error_message' => 'Phone number format is invalid'],
            ['error_code' => 'DUPLICATE_POLICY', 'error_message' => 'Policy already submitted'],
            ['error_code' => 'EXPIRED_POLICY', 'error_message' => 'Policy has already expired'],
            ['error_code' => 'SYSTEM_ERROR', 'error_message' => 'Partner system temporarily unavailable'],
        ];

        return array_merge(['success' => false], $errors[array_rand($errors)]);
    }

    /**
     * Translate error codes to human-readable messages
     */
    public static function getHumanErrorMessage(string $errorCode, string $originalMessage): string
    {
        $translations = [
            'INVALID_PHONE' => 'Nomor telepon tidak valid. Periksa format nomor HP.',
            'DUPLICATE_POLICY' => 'Polis sudah pernah dikirim sebelumnya.',
            'EXPIRED_POLICY' => 'Polis sudah melewati tanggal jatuh tempo.',
            'SYSTEM_ERROR' => 'Sistem partner sedang tidak tersedia. Akan dicoba ulang.',
            'TIMEOUT' => 'Koneksi ke partner timeout. Akan dicoba ulang.',
            'UNAUTHORIZED' => 'Autentikasi gagal. Hubungi administrator.',
            'EXCEPTION' => 'Terjadi kesalahan sistem: ' . $originalMessage,
        ];

        return $translations[$errorCode] ?? $originalMessage;
    }
}
