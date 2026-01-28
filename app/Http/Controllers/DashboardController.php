<?php

namespace App\Http\Controllers;

use App\Models\RenewalEvent;
use App\Models\RenewalData;
use App\Models\CallHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $activeEvents = RenewalEvent::where('status', 'active')->get();
        
        $stats = [
            'total_events' => RenewalEvent::count(),
            'active_events' => RenewalEvent::where('status', 'active')->count(),
            'total_data' => RenewalData::count(),
            'pending_calls' => RenewalData::where('call_status', 'pending')->count(),
            'renewed_today' => RenewalData::where('call_status', 'renewed')
                                          ->whereDate('updated_at', today())
                                          ->count(),
            'calls_today' => CallHistory::whereDate('called_at', today())->count(),
        ];

        $statusBreakdown = RenewalData::whereHas('renewalEvent', function($q) {
                $q->where('status', 'active');
            })
            ->select('call_status', DB::raw('count(*) as count'))
            ->groupBy('call_status')
            ->pluck('count', 'call_status')
            ->toArray();

        $recentCalls = CallHistory::with(['renewalData', 'calledByUser'])
            ->latest('called_at')
            ->take(10)
            ->get();

        $callbacksToday = RenewalData::with('renewalEvent')
            ->where('call_status', 'callback')
            ->whereDate('callback_at', today())
            ->take(10)
            ->get();

        return view('dashboard.index', compact(
            'activeEvents',
            'stats',
            'statusBreakdown',
            'recentCalls',
            'callbacksToday'
        ));
    }
}
