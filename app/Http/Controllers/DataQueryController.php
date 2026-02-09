<?php

namespace App\Http\Controllers;

use App\Models\DataBatch;
use App\Services\DataQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataQueryController extends Controller
{
    protected DataQueryService $queryService;

    public function __construct(DataQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Show data query page (UW only)
     */
    public function index()
    {
        $this->authorizeUnderwriter();

        $availableMonths = $this->queryService->getAvailableMonths();
        
        $batches = DataBatch::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('data-query.index', compact('availableMonths', 'batches'));
    }

    /**
     * Trigger data query for a specific month
     */
    public function query(Request $request)
    {
        $this->authorizeUnderwriter();

        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->month;

        // Check if batch already exists for this month
        $existingBatch = DataBatch::where('month', $month)
            ->whereNotIn('status', [DataBatch::STATUS_COMPLETED, DataBatch::STATUS_UW_REJECTED])
            ->first();

        if ($existingBatch) {
            return back()->with('error', "Batch for {$month} already exists and is still in progress.");
        }

        try {
            $batch = $this->queryService->queryDataForMonth($month, Auth::id());

            if ($batch->total_records > 0) {
                return redirect()->route('data-query.index')
                    ->with('success', "Successfully queried {$batch->total_records} records for {$batch->month_label}.");
            } else {
                return redirect()->route('data-query.index')
                    ->with('info', "No data found for {$batch->month_label}. The batch was created but is empty.");
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Query failed: ' . $e->getMessage());
        }
    }

    /**
     * View batch details
     */
    public function show(DataBatch $batch)
    {
        $this->authorizeUnderwriter();

        $batch->load(['renewalData', 'creator', 'uwApprover', 'marketingApprover']);
        $batch->updateCounts();

        return view('data-query.show', compact('batch'));
    }

    /**
     * Delete a batch (only if pending_uw)
     */
    public function destroy(DataBatch $batch)
    {
        $this->authorizeUnderwriter();

        if ($batch->status !== DataBatch::STATUS_PENDING_UW) {
            return back()->with('error', 'Cannot delete batch that has been processed.');
        }

        $batch->renewalData()->delete();
        $batch->delete();

        return redirect()->route('data-query.index')
            ->with('success', 'Batch deleted successfully.');
    }

    /**
     * Check if current user is Underwriter
     */
    protected function authorizeUnderwriter(): void
    {
        if (Auth::user()->role !== 'underwriter') {
            abort(403, 'Access denied. Underwriter role required.');
        }
    }
}
