<?php

namespace App\Http\Controllers;

use App\Models\RenewalEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $events = RenewalEvent::with('creator')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        return view('settings.index', compact('events'));
    }

    public function create()
    {
        return view('settings.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|in:renewal,upgrade',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,active,completed,cancelled',
        ]);

        $exists = RenewalEvent::where('event_type', $request->event_type)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'month' => 'An event for this type, year and month already exists.'
            ]);
        }

        RenewalEvent::create([
            ...$request->only(['name', 'event_type', 'year', 'month', 'start_date', 'end_date', 'description', 'status']),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index')
            ->with('success', 'Event created successfully.');
    }

    public function edit(RenewalEvent $event)
    {
        return view('settings.edit', compact('event'));
    }

    public function update(Request $request, RenewalEvent $event)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'event_type' => 'required|in:renewal,upgrade',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,active,completed,cancelled',
        ]);

        $exists = RenewalEvent::where('event_type', $request->event_type)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->where('id', '!=', $event->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'month' => 'An event for this type, year and month already exists.'
            ]);
        }

        $event->update($request->only([
            'name', 'event_type', 'year', 'month', 'start_date', 'end_date', 'description', 'status'
        ]));

        return redirect()->route('settings.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(RenewalEvent $event)
    {
        if ($event->renewalData()->count() > 0) {
            return back()->with('error', 'Cannot delete event with existing data.');
        }

        $event->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Event deleted successfully.');
    }

    public function toggleStatus(RenewalEvent $event)
    {
        $newStatus = $event->status === 'active' ? 'draft' : 'active';
        $event->update(['status' => $newStatus]);

        return back()->with('success', "Event status changed to {$newStatus}.");
    }
}
