<?php

namespace App\Http\Controllers;

use App\Models\DataBatch;
use App\Models\RenewalData;
use App\Exports\PartnerResultExport;
use App\Services\PartnerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selectedMonth = $request->get('month', now()->format('Y-m'));

        // Get available months
        $availableMonths = DataBatch::select('month')
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month')
            ->map(fn($m) => ['value' => $m, 'label' => \Carbon\Carbon::parse($m . '-01')->format('F Y')]);

        // If no batches yet, show current month
        if ($availableMonths->isEmpty()) {
            $availableMonths = collect([
                ['value' => now()->format('Y-m'), 'label' => now()->format('F Y')]
            ]);
        }

        // Stats for selected month
        $monthlyStats = $this->getMonthlyStats($selectedMonth);

        // Partner submission results for selected month
        $partnerResults = RenewalData::where('batch_month', $selectedMonth)
            ->whereIn('workflow_status', [
                RenewalData::STATUS_SENT,
                RenewalData::STATUS_SUCCESS,
                RenewalData::STATUS_FAILED,
            ])
            ->select('workflow_status', DB::raw('count(*) as total'))
            ->groupBy('workflow_status')
            ->pluck('total', 'workflow_status');

        // Failed breakdown by error
        $failedByError = RenewalData::where('batch_month', $selectedMonth)
            ->where('workflow_status', RenewalData::STATUS_FAILED)
            ->select('partner_error_code', 'partner_error_message', DB::raw('count(*) as total'))
            ->groupBy('partner_error_code', 'partner_error_message')
            ->orderBy('total', 'desc')
            ->get();

        // Recent success
        $recentSuccess = RenewalData::where('batch_month', $selectedMonth)
            ->where('workflow_status', RenewalData::STATUS_SUCCESS)
            ->orderBy('partner_sent_at', 'desc')
            ->limit(10)
            ->get();

        // Recent failed
        $recentFailed = RenewalData::where('batch_month', $selectedMonth)
            ->where('workflow_status', RenewalData::STATUS_FAILED)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        // Batches for selected month
        $batches = DataBatch::where('month', $selectedMonth)
            ->with(['uwApprover', 'marketingApprover'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Check if sending is allowed
        $partnerService = new PartnerService();
        $sendingAllowed = $partnerService->isSendingAllowed();

        return view('dashboard', compact(
            'user',
            'selectedMonth',
            'availableMonths',
            'monthlyStats',
            'partnerResults',
            'failedByError',
            'recentSuccess',
            'recentFailed',
            'batches',
            'sendingAllowed'
        ));
    }

    /**
     * Export partner results for a month
     */
    public function export(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'type' => 'required|in:all,success,failed',
        ]);

        $month = $request->month;
        $type = $request->type;
        $monthLabel = \Carbon\Carbon::parse($month . '-01')->format('F_Y');

        $filename = "Partner_Results_{$monthLabel}_{$type}.xlsx";

        return Excel::download(new PartnerResultExport($month, $type), $filename);
    }

    protected function getMonthlyStats(string $month): array
    {
        $query = RenewalData::where('batch_month', $month);

        return [
            'total' => (clone $query)->count(),
            'pending_uw' => (clone $query)->where('workflow_status', RenewalData::STATUS_PENDING_UW)->count(),
            'uw_approved' => (clone $query)->where('workflow_status', RenewalData::STATUS_UW_APPROVED)->count(),
            'uw_rejected' => (clone $query)->where('workflow_status', RenewalData::STATUS_UW_REJECTED)->count(),
            'pending_marketing' => (clone $query)->where('workflow_status', RenewalData::STATUS_PENDING_MARKETING)->count(),
            'marketing_approved' => (clone $query)->where('workflow_status', RenewalData::STATUS_MARKETING_APPROVED)->count(),
            'sent' => (clone $query)->where('workflow_status', RenewalData::STATUS_SENT)->count(),
            'success' => (clone $query)->where('workflow_status', RenewalData::STATUS_SUCCESS)->count(),
            'failed' => (clone $query)->where('workflow_status', RenewalData::STATUS_FAILED)->count(),
        ];
    }
}
