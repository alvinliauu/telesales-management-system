<?php

namespace App\Http\Controllers;

use App\Models\DataBatch;
use App\Models\RenewalData;
use App\Exports\RenewalDataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MarketingReviewController extends Controller
{
    /**
     * Show Marketing review list
     */
    public function index(Request $request)
    {
        $this->authorizeMarketing();

        $query = DataBatch::with(['uwApprover'])
            ->whereIn('status', [
                DataBatch::STATUS_PENDING_MARKETING,
                DataBatch::STATUS_MARKETING_APPROVED,
                DataBatch::STATUS_PROCESSING,
                DataBatch::STATUS_COMPLETED,
            ]);

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get available months for filter
        $months = DataBatch::select('month')
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month')
            ->map(fn($m) => ['value' => $m, 'label' => \Carbon\Carbon::parse($m . '-01')->format('F Y')]);

        return view('marketing-review.index', compact('batches', 'months'));
    }

    /**
     * Show batch detail for Marketing review
     */
    public function show(DataBatch $batch, Request $request)
    {
        $this->authorizeMarketing();

        if (!in_array($batch->status, [
            DataBatch::STATUS_PENDING_MARKETING,
            DataBatch::STATUS_MARKETING_APPROVED,
            DataBatch::STATUS_PROCESSING,
            DataBatch::STATUS_COMPLETED,
        ])) {
            return redirect()->route('marketing-review.index')
                ->with('error', 'Batch not available for Marketing review.');
        }

        $query = $batch->renewalData()
            ->whereIn('workflow_status', [
                RenewalData::STATUS_PENDING_MARKETING,
                RenewalData::STATUS_MARKETING_APPROVED,
                RenewalData::STATUS_SENT,
                RenewalData::STATUS_SUCCESS,
                RenewalData::STATUS_FAILED,
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_kontrak', 'like', "%{$search}%")
                  ->orWhere('no_polis', 'like', "%{$search}%")
                  ->orWhere('nama_tertanggung', 'like', "%{$search}%");
            });
        }

        if ($request->filled('workflow_status')) {
            $query->where('workflow_status', $request->workflow_status);
        }

        $records = $query->orderBy('id')->paginate(50);
        $batch->updateCounts();

        return view('marketing-review.show', compact('batch', 'records'));
    }

    /**
     * Download batch data as Excel
     */
    public function download(DataBatch $batch)
    {
        $this->authorizeMarketing();

        $filename = "Marketing_Review_{$batch->batch_number}.xlsx";
        
        return Excel::download(new RenewalDataExport($batch->id, 'marketing'), $filename);
    }

    /**
     * Approve entire batch to send to partner
     */
    public function approveBatch(DataBatch $batch)
    {
        $this->authorizeMarketing();

        if ($batch->status !== DataBatch::STATUS_PENDING_MARKETING) {
            return back()->with('error', 'Batch is not pending Marketing review.');
        }

        DB::transaction(function() use ($batch) {
            // Move all pending marketing records to marketing approved
            $batch->renewalData()
                ->where('workflow_status', RenewalData::STATUS_PENDING_MARKETING)
                ->update([
                    'workflow_status' => RenewalData::STATUS_MARKETING_APPROVED,
                    'marketing_approved_by' => Auth::id(),
                    'marketing_approved_at' => now(),
                ]);

            // Update batch status
            $batch->update([
                'status' => DataBatch::STATUS_MARKETING_APPROVED,
                'marketing_approved_by' => Auth::id(),
                'marketing_approved_at' => now(),
            ]);

            $batch->updateCounts();
        });

        return redirect()->route('marketing-review.index')
            ->with('success', "Batch {$batch->batch_number} approved. Data will be sent to partner by scheduler.");
    }

    /**
     * Return batch to UW for revision
     */
    public function returnToUw(Request $request, DataBatch $batch)
    {
        $this->authorizeMarketing();

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($batch->status !== DataBatch::STATUS_PENDING_MARKETING) {
            return back()->with('error', 'Batch is not pending Marketing review.');
        }

        DB::transaction(function() use ($batch, $request) {
            // Move records back to pending UW
            $batch->renewalData()
                ->where('workflow_status', RenewalData::STATUS_PENDING_MARKETING)
                ->update([
                    'workflow_status' => RenewalData::STATUS_PENDING_UW,
                    'marketing_notes' => $request->reason,
                ]);

            // Update batch status
            $batch->update([
                'status' => DataBatch::STATUS_PENDING_UW,
                'notes' => 'Returned by Marketing: ' . $request->reason,
            ]);

            $batch->updateCounts();
        });

        return redirect()->route('marketing-review.index')
            ->with('success', "Batch {$batch->batch_number} returned to Underwriter for revision.");
    }

    protected function authorizeMarketing(): void
    {
        if (Auth::user()->role !== 'marketing') {
            abort(403, 'Access denied. Marketing role required.');
        }
    }
}
