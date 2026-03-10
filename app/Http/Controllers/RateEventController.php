<?php

namespace App\Http\Controllers;

use App\Models\RateEvent;
use App\Models\RateEventRule;
use App\Models\Zone;
use App\Models\VehicleType;
use App\Models\CoverageType;
use App\Models\VehiclePriceCategory;
use App\Models\TransactionType;
use App\Models\TsiOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RateEventController extends Controller
{
    /**
     * Check if user can edit (Marketing or Admin only)
     */
    protected function canEdit(): bool
    {
        $user = Auth::user();
        return in_array($user->role, ['admin', 'super_admin', 'marketing']);
    }

    /**
     * Display a listing of rate events
     */
    public function index(Request $request)
    {
        $query = RateEvent::with(['creator', 'approver'])
            ->withCount('rules');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('start_date', 'desc')->paginate(15);

        return view('rate-events.index', [
            'events' => $events,
            'canEdit' => $this->canEdit(),
            'statusOptions' => RateEvent::statusOptions(),
        ]);
    }

    /**
     * Show the form for creating a new event
     */
    public function create()
    {
        if (!$this->canEdit()) {
            return redirect()->route('rate-events.index')
                ->with('error', 'Anda tidak memiliki akses untuk membuat event.');
        }

        return view('rate-events.create', [
            'vehicleTypes' => VehicleType::where('is_active', true)->get(),
            'coverageTypes' => CoverageType::where('is_active', true)->orderBy('sort_order')->get(),
            'zones' => Zone::where('is_active', true)->get(),
            'priceCategories' => VehiclePriceCategory::where('is_active', true)->get(),
            'transactionTypes' => TransactionType::where('is_active', true)->get(),
            'tsiOptions' => TsiOption::with('coverageType')->where('is_active', true)->get(),
            'overrideTypes' => RateEventRule::overrideTypeOptions(),
        ]);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        if (!$this->canEdit()) {
            return redirect()->route('rate-events.index')
                ->with('error', 'Anda tidak memiliki akses untuk membuat event.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'nullable|integer|min:0',
        ]);

        // Generate code
        $code = 'EVT-' . strtoupper(Str::slug(Str::limit($validated['name'], 20, ''))) . '-' . date('Ymd');
        $counter = 1;
        while (RateEvent::where('code', $code)->exists()) {
            $code = 'EVT-' . strtoupper(Str::slug(Str::limit($validated['name'], 20, ''))) . '-' . date('Ymd') . '-' . $counter;
            $counter++;
        }

        $event = RateEvent::create([
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => RateEvent::STATUS_DRAFT,
            'priority' => $validated['priority'] ?? 0,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('rate-events.edit', $event)
            ->with('success', 'Event berhasil dibuat. Silakan tambahkan rules.');
    }

    /**
     * Display the specified event
     */
    public function show(RateEvent $rateEvent)
    {
        $rateEvent->load([
            'rules.vehicleType',
            'rules.coverageType',
            'rules.zone',
            'rules.vehiclePriceCategory',
            'rules.transactionType',
            'rules.tsiOption',
            'creator',
            'approver',
        ]);

        return view('rate-events.show', [
            'event' => $rateEvent,
            'canEdit' => $this->canEdit(),
        ]);
    }

    /**
     * Show the form for editing the specified event
     */
    public function edit(RateEvent $rateEvent)
    {
        if (!$this->canEdit()) {
            return redirect()->route('rate-events.show', $rateEvent)
                ->with('error', 'Anda tidak memiliki akses untuk edit event.');
        }

        $rateEvent->load([
            'rules.vehicleType',
            'rules.coverageType',
            'rules.zone',
            'rules.vehiclePriceCategory',
            'rules.transactionType',
            'rules.tsiOption',
        ]);

        return view('rate-events.edit', [
            'event' => $rateEvent,
            'vehicleTypes' => VehicleType::where('is_active', true)->get(),
            'coverageTypes' => CoverageType::where('is_active', true)->orderBy('sort_order')->get(),
            'zones' => Zone::where('is_active', true)->get(),
            'priceCategories' => VehiclePriceCategory::where('is_active', true)->get(),
            'transactionTypes' => TransactionType::where('is_active', true)->get(),
            'tsiOptions' => TsiOption::with('coverageType')->where('is_active', true)->orderBy('coverage_type_id')->orderBy('sort_order')->get(),
            'overrideTypes' => RateEventRule::overrideTypeOptions(),
        ]);
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, RateEvent $rateEvent)
    {
        if (!$this->canEdit()) {
            return redirect()->route('rate-events.show', $rateEvent)
                ->with('error', 'Anda tidak memiliki akses untuk edit event.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'nullable|integer|min:0',
        ]);

        $rateEvent->update($validated);

        return redirect()->route('rate-events.edit', $rateEvent)
            ->with('success', 'Event berhasil diupdate.');
    }

    /**
     * Activate the event
     */
    public function activate(RateEvent $rateEvent)
    {
        if (!$this->canEdit()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        if ($rateEvent->rules()->count() === 0) {
            return back()->with('error', 'Event harus memiliki minimal 1 rule.');
        }

        $rateEvent->update([
            'status' => RateEvent::STATUS_ACTIVE,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Event berhasil diaktifkan.');
    }

    /**
     * Cancel the event
     */
    public function cancel(RateEvent $rateEvent)
    {
        if (!$this->canEdit()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        $rateEvent->update([
            'status' => RateEvent::STATUS_CANCELLED,
        ]);

        return back()->with('success', 'Event berhasil dibatalkan.');
    }

    /**
     * Delete the event
     */
    public function destroy(RateEvent $rateEvent)
    {
        if (!$this->canEdit()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        if ($rateEvent->status === RateEvent::STATUS_ACTIVE) {
            return back()->with('error', 'Tidak bisa hapus event yang sedang aktif. Cancel dulu.');
        }

        $rateEvent->delete();

        return redirect()->route('rate-events.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    /**
     * Add a rule to the event
     */
    public function addRule(Request $request, RateEvent $rateEvent)
    {
        if (!$this->canEdit()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'coverage_type_id' => 'nullable|exists:coverage_types,id',
            'zone_id' => 'nullable|exists:zones,id',
            'vehicle_price_category_id' => 'nullable|exists:vehicle_price_categories,id',
            'transaction_type_id' => 'nullable|exists:transaction_types,id',
            'tsi_option_id' => 'nullable|exists:tsi_options,id',
            'override_type' => 'required|in:' . implode(',', array_keys(RateEventRule::overrideTypeOptions())),
            'custom_rate' => 'nullable|numeric|min:0|max:100',
            'flat_amount' => 'nullable|numeric|min:0',
            'percentage_value' => 'nullable|numeric|min:0|max:100',
            'tsi_add_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'only_if_previous_exists' => 'nullable|boolean',
            'previous_condition' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['rate_event_id'] = $rateEvent->id;
        $validated['is_active'] = true;

        RateEventRule::create($validated);

        return back()->with('success', 'Rule berhasil ditambahkan.');
    }

    /**
     * Update a rule
     */
    public function updateRule(Request $request, RateEvent $rateEvent, RateEventRule $rule)
    {
        if (!$this->canEdit()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        $validated = $request->validate([
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'coverage_type_id' => 'nullable|exists:coverage_types,id',
            'zone_id' => 'nullable|exists:zones,id',
            'vehicle_price_category_id' => 'nullable|exists:vehicle_price_categories,id',
            'transaction_type_id' => 'nullable|exists:transaction_types,id',
            'tsi_option_id' => 'nullable|exists:tsi_options,id',
            'override_type' => 'required|in:' . implode(',', array_keys(RateEventRule::overrideTypeOptions())),
            'custom_rate' => 'nullable|numeric|min:0|max:100',
            'flat_amount' => 'nullable|numeric|min:0',
            'percentage_value' => 'nullable|numeric|min:0|max:100',
            'tsi_add_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'only_if_previous_exists' => 'nullable|boolean',
            'previous_condition' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $rule->update($validated);

        return back()->with('success', 'Rule berhasil diupdate.');
    }

    /**
     * Delete a rule
     */
    public function deleteRule(RateEvent $rateEvent, RateEventRule $rule)
    {
        if (!$this->canEdit()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        $rule->delete();

        return back()->with('success', 'Rule berhasil dihapus.');
    }

    /**
     * Duplicate an event
     */
    public function duplicate(RateEvent $rateEvent)
    {
        if (!$this->canEdit()) {
            return back()->with('error', 'Anda tidak memiliki akses.');
        }

        DB::transaction(function () use ($rateEvent) {
            $newEvent = $rateEvent->replicate();
            $newEvent->code = $rateEvent->code . '-COPY-' . date('His');
            $newEvent->name = $rateEvent->name . ' (Copy)';
            $newEvent->status = RateEvent::STATUS_DRAFT;
            $newEvent->created_by = Auth::id();
            $newEvent->approved_by = null;
            $newEvent->approved_at = null;
            $newEvent->save();

            foreach ($rateEvent->rules as $rule) {
                $newRule = $rule->replicate();
                $newRule->rate_event_id = $newEvent->id;
                $newRule->save();
            }
        });

        return redirect()->route('rate-events.index')
            ->with('success', 'Event berhasil diduplikasi.');
    }
}
