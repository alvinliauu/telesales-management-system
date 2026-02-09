<?php

namespace App\Http\Controllers;

use App\Models\DataBatch;
use App\Models\RenewalData;
use App\Imports\RenewalDataImport;
use App\Exports\RenewalDataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UwReviewController extends Controller
{
    /**
     * Show UW review list
     */
    public function index(Request $request)
    {
        $this->authorizeUnderwriter();

        $query = DataBatch::with('creator')
            ->whereIn('status', [
                DataBatch::STATUS_PENDING_UW,
                DataBatch::STATUS_UW_APPROVED,
                DataBatch::STATUS_UW_REJECTED,
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

        return view('uw-review.index', compact('batches', 'months'));
    }

    /**
     * Show batch detail for review
     */
    public function show(DataBatch $batch, Request $request)
    {
        $this->authorizeUnderwriter();

        $query = $batch->renewalData();

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

        return view('uw-review.show', compact('batch', 'records'));
    }

    /**
     * Download batch data as Excel
     */
    public function download(DataBatch $batch)
    {
        $this->authorizeUnderwriter();

        $filename = "UW_Review_{$batch->batch_number}.xlsx";
        
        return Excel::download(new RenewalDataExport($batch->id), $filename);
    }

    /**
     * Upload revised data (reupload after edit)
     */
    public function upload(Request $request, DataBatch $batch)
    {
        $this->authorizeUnderwriter();

        if ($batch->status !== DataBatch::STATUS_PENDING_UW) {
            return back()->with('error', 'Cannot upload to batch that has already been processed.');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        try {
            // Clear existing data in batch
            $batch->renewalData()->delete();

            // Import new data
            $import = new RenewalDataImport(null, null, $batch->id, $batch->month);
            Excel::import($import, $request->file('file'));

            $batch->update([
                'total_records' => $import->getSuccessCount(),
            ]);

            return back()->with('success', "Uploaded {$import->getSuccessCount()} records successfully.");

        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve single record
     */
    public function approveRecord(RenewalData $record)
    {
        $this->authorizeUnderwriter();

        if ($record->workflow_status !== RenewalData::STATUS_PENDING_UW) {
            return back()->with('error', 'Record is not pending UW review.');
        }

        $record->update([
            'workflow_status' => RenewalData::STATUS_UW_APPROVED,
            'uw_approved_by' => Auth::id(),
            'uw_approved_at' => now(),
        ]);

        $record->batch?->updateCounts();

        return back()->with('success', 'Record approved.');
    }

    /**
     * Reject single record
     */
    public function rejectRecord(Request $request, RenewalData $record)
    {
        $this->authorizeUnderwriter();

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($record->workflow_status !== RenewalData::STATUS_PENDING_UW) {
            return back()->with('error', 'Record is not pending UW review.');
        }

        $record->update([
            'workflow_status' => RenewalData::STATUS_UW_REJECTED,
            'uw_rejection_reason' => $request->reason,
            'uw_approved_by' => Auth::id(),
            'uw_approved_at' => now(),
        ]);

        $record->batch?->updateCounts();

        return back()->with('success', 'Record rejected.');
    }

    /**
     * Bulk approve selected records
     */
    public function bulkApprove(Request $request)
    {
        $this->authorizeUnderwriter();

        $request->validate([
            'record_ids' => 'required|array|min:1',
            'record_ids.*' => 'exists:renewal_data,id',
        ]);

        $count = RenewalData::whereIn('id', $request->record_ids)
            ->where('workflow_status', RenewalData::STATUS_PENDING_UW)
            ->update([
                'workflow_status' => RenewalData::STATUS_UW_APPROVED,
                'uw_approved_by' => Auth::id(),
                'uw_approved_at' => now(),
            ]);

        // Update batch counts
        $batchIds = RenewalData::whereIn('id', $request->record_ids)->pluck('batch_id')->unique();
        foreach ($batchIds as $batchId) {
            DataBatch::find($batchId)?->updateCounts();
        }

        return back()->with('success', "{$count} records approved.");
    }

    /**
     * Bulk reject selected records
     */
    public function bulkReject(Request $request)
    {
        $this->authorizeUnderwriter();

        $request->validate([
            'record_ids' => 'required|array|min:1',
            'record_ids.*' => 'exists:renewal_data,id',
            'reason' => 'required|string|max:500',
        ]);

        $count = RenewalData::whereIn('id', $request->record_ids)
            ->where('workflow_status', RenewalData::STATUS_PENDING_UW)
            ->update([
                'workflow_status' => RenewalData::STATUS_UW_REJECTED,
                'uw_rejection_reason' => $request->reason,
                'uw_approved_by' => Auth::id(),
                'uw_approved_at' => now(),
            ]);

        // Update batch counts
        $batchIds = RenewalData::whereIn('id', $request->record_ids)->pluck('batch_id')->unique();
        foreach ($batchIds as $batchId) {
            DataBatch::find($batchId)?->updateCounts();
        }

        return back()->with('success', "{$count} records rejected.");
    }

    /**
     * Approve entire batch and move to Marketing
     */
    public function approveBatch(DataBatch $batch)
    {
        $this->authorizeUnderwriter();

        if ($batch->status !== DataBatch::STATUS_PENDING_UW) {
            return back()->with('error', 'Batch is not pending UW review.');
        }

        // Check if there are any pending records
        $pendingCount = $batch->renewalData()->where('workflow_status', RenewalData::STATUS_PENDING_UW)->count();
        
        if ($pendingCount > 0) {
            return back()->with('error', "Cannot approve batch. {$pendingCount} records still pending review.");
        }

        DB::transaction(function() use ($batch) {
            // Move all UW approved records to pending marketing
            $batch->renewalData()
                ->where('workflow_status', RenewalData::STATUS_UW_APPROVED)
                ->update([
                    'workflow_status' => RenewalData::STATUS_PENDING_MARKETING,
                ]);

            // Update batch status
            $batch->update([
                'status' => DataBatch::STATUS_PENDING_MARKETING,
                'uw_approved_by' => Auth::id(),
                'uw_approved_at' => now(),
            ]);

            $batch->updateCounts();
        });

        return redirect()->route('uw-review.index')
            ->with('success', "Batch {$batch->batch_number} approved and sent to Marketing.");
    }

    /**
     * Reject entire batch
     */
    public function rejectBatch(Request $request, DataBatch $batch)
    {
        $this->authorizeUnderwriter();

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($batch->status !== DataBatch::STATUS_PENDING_UW) {
            return back()->with('error', 'Batch is not pending UW review.');
        }

        $batch->update([
            'status' => DataBatch::STATUS_UW_REJECTED,
            'uw_approved_by' => Auth::id(),
            'uw_approved_at' => now(),
            'notes' => $request->reason,
        ]);

        return redirect()->route('uw-review.index')
            ->with('success', "Batch {$batch->batch_number} rejected.");
    }

    protected function authorizeUnderwriter(): void
    {
        if (Auth::user()->role !== 'underwriter') {
            abort(403, 'Access denied. Underwriter role required.');
        }
    }
}
