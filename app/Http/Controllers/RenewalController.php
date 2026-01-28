<?php

namespace App\Http\Controllers;

use App\Models\RenewalEvent;
use App\Models\RenewalData;
use App\Models\CallHistory;
use App\Models\UploadLog;
use App\Imports\RenewalDataImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $query = RenewalData::with(['renewalEvent', 'assignedUser', 'lastCalledByUser'])
            ->when($selectedEventId, fn($q) => $q->where('renewal_event_id', $selectedEventId));

        if ($request->filled('status')) {
            $query->where('call_status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_tertanggung', 'like', "%{$search}%")
                  ->orWhere('no_kontrak', 'like', "%{$search}%")
                  ->orWhere('no_polis', 'like', "%{$search}%")
                  ->orWhere('ano', 'like', "%{$search}%");
            });
        }

        $renewals = $query->orderBy('end_date', 'asc')->paginate(25);
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
            $path = $file->store('uploads/renewal');
            $import = new RenewalDataImport($event->id, $uploadLog->id);
            Excel::import($import, storage_path('app/' . $path));

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
        $renewal->load(['renewalEvent', 'assignedUser', 'callHistories.calledByUser']);
        return view('telesales.renewal.show', compact('renewal'));
    }

    public function updateCall(Request $request, RenewalData $renewal)
    {
        $request->validate([
            'result' => 'required|in:answered,no_answer,busy,voicemail,wrong_number,callback_requested,interested,renewed,declined',
            'notes' => 'nullable|string|max:1000',
            'callback_date' => 'nullable|date|after:now',
            'selected_package' => 'nullable|string',
            'agreed_premium' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function() use ($request, $renewal) {
            CallHistory::create([
                'renewal_data_id' => $renewal->id,
                'called_by' => Auth::id(),
                'called_at' => now(),
                'result' => $request->result,
                'notes' => $request->notes,
                'callback_scheduled' => $request->callback_date,
            ]);

            $statusMap = [
                'answered' => 'called',
                'no_answer' => 'no_answer',
                'busy' => 'no_answer',
                'voicemail' => 'no_answer',
                'wrong_number' => 'invalid_contact',
                'callback_requested' => 'callback',
                'interested' => 'interested',
                'renewed' => 'renewed',
                'declined' => 'declined',
            ];

            $updateData = [
                'call_status' => $statusMap[$request->result] ?? 'called',
                'call_notes' => $request->notes,
                'last_call_at' => now(),
                'last_called_by' => Auth::id(),
            ];

            if ($request->result === 'callback_requested' && $request->callback_date) {
                $updateData['callback_at'] = $request->callback_date;
            }

            if ($request->result === 'renewed') {
                $updateData['selected_package'] = $request->selected_package;
                $updateData['agreed_premium'] = $request->agreed_premium;
            }

            $renewal->update($updateData);
        });

        return back()->with('success', 'Call status updated successfully.');
    }

    public function nextCall(Request $request)
    {
        $eventId = $request->get('event_id');

        $next = RenewalData::when($eventId, fn($q) => $q->where('renewal_event_id', $eventId))
            ->where(function($q) {
                $q->where('call_status', 'pending')
                  ->orWhere(function($q2) {
                      $q2->where('call_status', 'callback')
                         ->where('callback_at', '<=', now());
                  });
            })
            ->orderByRaw("CASE WHEN call_status = 'callback' THEN 0 ELSE 1 END")
            ->orderBy('callback_at', 'asc')
            ->orderBy('end_date', 'asc')
            ->first();

        if (!$next) {
            return redirect()->route('telesales.renewal.index', ['event_id' => $eventId])
                ->with('info', 'No pending calls available.');
        }

        return redirect()->route('telesales.renewal.show', $next);
    }

    private function getEventStatistics($eventId): array
    {
        $data = RenewalData::where('renewal_event_id', $eventId);
        
        return [
            'total' => (clone $data)->count(),
            'pending' => (clone $data)->where('call_status', 'pending')->count(),
            'called' => (clone $data)->whereNotIn('call_status', ['pending'])->count(),
            'renewed' => (clone $data)->where('call_status', 'renewed')->count(),
            'declined' => (clone $data)->where('call_status', 'declined')->count(),
            'callback' => (clone $data)->where('call_status', 'callback')->count(),
            'no_answer' => (clone $data)->where('call_status', 'no_answer')->count(),
            'interested' => (clone $data)->where('call_status', 'interested')->count(),
            'total_premium' => (clone $data)->where('call_status', 'renewed')->sum('agreed_premium'),
        ];
    }
}
