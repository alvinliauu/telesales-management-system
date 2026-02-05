<?php

namespace App\Http\Controllers;

use App\Models\RenewalEvent;
use App\Models\RenewalData;
use App\Models\UploadLog;
use App\Imports\RenewalDataImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class RenewalController extends Controller
{
    public function index(Request $request)
    {
        $events = RenewalEvent::where('event_type', 'renewal')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $selectedEventId = $request->get('event_id', $events->first()?->id);
        $selectedEvent = RenewalEvent::find($selectedEventId);

        $query = RenewalData::with(['renewalEvent'])
            ->when($selectedEventId, fn($q) => $q->where('renewal_event_id', $selectedEventId));

        // Partner status filter
        if ($request->filled('partner_status')) {
            $query->where('partner_status', $request->partner_status);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_tertanggung', 'like', "%{$search}%")
                  ->orWhere('no_kontrak', 'like', "%{$search}%")
                  ->orWhere('no_polis', 'like', "%{$search}%")
                  ->orWhere('ano', 'like', "%{$search}%")
                  ->orWhere('partner_request_id', 'like', "%{$search}%");
            });
        }

        $renewals = $query->orderBy('created_at', 'desc')->paginate(25);
        $statistics = $selectedEvent ? $this->getEventStatistics($selectedEventId) : null;

        return view('telesales.renewal.index', compact(
            'events',
            'selectedEvent',
            'renewals',
            'statistics'
        ));
    }

    public function showUpload()
    {
        $events = RenewalEvent::where('event_type', 'renewal')
            ->where('status', 'active')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $recentUploads = UploadLog::with(['renewalEvent', 'uploadedByUser'])
            ->latest()
            ->take(10)
            ->get();

        return view('telesales.renewal.upload', compact('events', 'recentUploads'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'renewal_event_id' => 'required|exists:renewal_events,id',
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $event = RenewalEvent::findOrFail($request->renewal_event_id);

        $uploadLog = UploadLog::create([
            'renewal_event_id' => $event->id,
            'filename' => $file->hashName(),
            'original_filename' => $file->getClientOriginalName(),
            'status' => 'processing',
            'uploaded_by' => Auth::id(),
        ]);

        try {
            $import = new RenewalDataImport($event->id, $uploadLog->id);
            Excel::import($import, $file);

            $uploadLog->update([
                'total_rows' => $import->getRowCount(),
                'success_rows' => $import->getSuccessCount(),
                'failed_rows' => $import->getFailedCount(),
                'errors' => $import->getErrors(),
                'status' => 'completed',
            ]);

            return redirect()->route('telesales.renewal.index', ['event_id' => $event->id])
                ->with('success', "Upload completed! {$import->getSuccessCount()} rows imported successfully.");

        } catch (\Exception $e) {
            $uploadLog->update([
                'status' => 'failed',
                'errors' => ['message' => $e->getMessage()],
            ]);

            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function show(RenewalData $renewal)
    {
        $renewal->load(['renewalEvent']);
        return view('telesales.renewal.show', compact('renewal'));
    }

    private function getEventStatistics($eventId): array
    {
        $data = RenewalData::where('renewal_event_id', $eventId);
        
        return [
            'total' => (clone $data)->count(),
            'pending' => (clone $data)->where('partner_status', 'pending')->count(),
            'queued' => (clone $data)->where('partner_status', 'queued')->count(),
            'success' => (clone $data)->where('partner_status', 'success')->count(),
            'failed' => (clone $data)->where('partner_status', 'failed')->count(),
        ];
    }
}
