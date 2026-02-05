<?php

namespace App\Http\Controllers;

use App\Models\RenewalData;
use App\Models\RenewalEvent;
use App\Services\PartnerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get active events
        $activeEvents = RenewalEvent::where('status', 'active')->get();

        // Partner submission statistics
        $partnerStats = [
            'pending' => RenewalData::where('partner_status', 'pending')->count(),
            'queued' => RenewalData::where('partner_status', 'queued')->count(),
            'success' => RenewalData::where('partner_status', 'success')->count(),
            'failed' => RenewalData::where('partner_status', 'failed')->count(),
        ];

        // Failed submissions grouped by error code
        $failedByError = RenewalData::where('partner_status', 'failed')
            ->select('partner_error_code', 'partner_error_message', DB::raw('count(*) as total'))
            ->groupBy('partner_error_code', 'partner_error_message')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'error_code' => $item->partner_error_code,
                    'error_message' => $item->partner_error_message,
                    'total' => $item->total,
                ];
            });

        // Recent successful submissions
        $recentSuccess = RenewalData::where('partner_status', 'success')
            ->orderBy('partner_sent_at', 'desc')
            ->limit(10)
            ->get();

        // Recent failed submissions
        $recentFailed = RenewalData::where('partner_status', 'failed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Check if sending is currently allowed
        $partnerService = new PartnerService();
        $sendingAllowed = $partnerService->isSendingAllowed();

        // Event statistics
        $eventStats = RenewalEvent::where('status', 'active')
            ->withCount(['renewalData as total_data'])
            ->withCount(['renewalData as success_count' => function ($query) {
                $query->where('partner_status', 'success');
            }])
            ->withCount(['renewalData as failed_count' => function ($query) {
                $query->where('partner_status', 'failed');
            }])
            ->withCount(['renewalData as pending_count' => function ($query) {
                $query->where('partner_status', 'pending');
            }])
            ->get();

        return view('dashboard', compact(
            'activeEvents',
            'partnerStats',
            'failedByError',
            'recentSuccess',
            'recentFailed',
            'sendingAllowed',
            'eventStats'
        ));
    }
}
