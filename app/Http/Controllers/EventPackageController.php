<?php

namespace App\Http\Controllers;

use App\Models\RenewalEvent;
use App\Models\EventPackage;
use App\Models\EventPackageExtension;
use App\Models\CarType;
use App\Models\Extension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventPackageController extends Controller
{
    public function index(RenewalEvent $event)
    {
        $event->load(['packages.packageExtensions.extension', 'packages.packageExtensions.carType']);
        $carTypes = CarType::active()->ordered()->get();
        
        return view('events.packages.index', compact('event', 'carTypes'));
    }

    public function create(RenewalEvent $event)
    {
        $carTypes = CarType::active()->ordered()->get();
        $extensions = Extension::active()->ordered()->get();
        $mainCoverages = Extension::active()->mainCoverage()->get();
        
        return view('events.packages.create', compact('event', 'carTypes', 'extensions', 'mainCoverages'));
    }

    public function store(Request $request, RenewalEvent $event)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'extensions' => 'required|array|min:1',
            'extensions.*.id' => 'required|exists:extensions,id',
            'extensions.*.rates' => 'required|array',
            'extensions.*.rates.*.car_type_id' => 'required|exists:car_types,id',
            'extensions.*.rates.*.rate_type' => 'required|in:percentage,flat',
            'extensions.*.rates.*.rate_value' => 'required|numeric|min:0',
        ]);

        // Validate: no two main coverages in same package
        $extensionIds = collect($request->extensions)->pluck('id');
        $mainCoverageCount = Extension::whereIn('id', $extensionIds)->where('is_main_coverage', true)->count();
        
        if ($mainCoverageCount > 1) {
            return back()->withInput()->withErrors([
                'extensions' => 'A package cannot have more than one main coverage (TLO/COMPREHENSIVE).'
            ]);
        }

        DB::transaction(function () use ($request, $event) {
            $package = EventPackage::create([
                'renewal_event_id' => $event->id,
                'name' => $request->name,
                'code' => Str::slug($request->name, '_'),
                'description' => $request->description,
                'is_active' => true,
                'sort_order' => $event->packages()->count(),
            ]);

            foreach ($request->extensions as $ext) {
                foreach ($ext['rates'] as $rate) {
                    EventPackageExtension::create([
                        'event_package_id' => $package->id,
                        'extension_id' => $ext['id'],
                        'car_type_id' => $rate['car_type_id'],
                        'rate_type' => $rate['rate_type'],
                        'rate_value' => $rate['rate_value'],
                    ]);
                }
            }
        });

        return redirect()->route('events.packages.index', $event)
            ->with('success', 'Package created successfully.');
    }

    public function edit(RenewalEvent $event, EventPackage $package)
    {
        $package->load('packageExtensions.extension', 'packageExtensions.carType');
        $carTypes = CarType::active()->ordered()->get();
        $extensions = Extension::active()->ordered()->get();
        $mainCoverages = Extension::active()->mainCoverage()->get();

        // Group existing rates by extension
        $existingRates = $package->packageExtensions->groupBy('extension_id');

        return view('events.packages.edit', compact('event', 'package', 'carTypes', 'extensions', 'mainCoverages', 'existingRates'));
    }

    public function update(Request $request, RenewalEvent $event, EventPackage $package)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'extensions' => 'required|array|min:1',
            'extensions.*.id' => 'required|exists:extensions,id',
            'extensions.*.rates' => 'required|array',
            'extensions.*.rates.*.car_type_id' => 'required|exists:car_types,id',
            'extensions.*.rates.*.rate_type' => 'required|in:percentage,flat',
            'extensions.*.rates.*.rate_value' => 'required|numeric|min:0',
        ]);

        // Validate: no two main coverages
        $extensionIds = collect($request->extensions)->pluck('id');
        $mainCoverageCount = Extension::whereIn('id', $extensionIds)->where('is_main_coverage', true)->count();
        
        if ($mainCoverageCount > 1) {
            return back()->withInput()->withErrors([
                'extensions' => 'A package cannot have more than one main coverage (TLO/COMPREHENSIVE).'
            ]);
        }

        DB::transaction(function () use ($request, $package) {
            $package->update([
                'name' => $request->name,
                'code' => Str::slug($request->name, '_'),
                'description' => $request->description,
            ]);

            // Delete old extensions
            $package->packageExtensions()->delete();

            // Add new extensions
            foreach ($request->extensions as $ext) {
                foreach ($ext['rates'] as $rate) {
                    EventPackageExtension::create([
                        'event_package_id' => $package->id,
                        'extension_id' => $ext['id'],
                        'car_type_id' => $rate['car_type_id'],
                        'rate_type' => $rate['rate_type'],
                        'rate_value' => $rate['rate_value'],
                    ]);
                }
            }
        });

        return redirect()->route('events.packages.index', $event)
            ->with('success', 'Package updated successfully.');
    }

    public function destroy(RenewalEvent $event, EventPackage $package)
    {
        $package->delete();

        return redirect()->route('events.packages.index', $event)
            ->with('success', 'Package deleted successfully.');
    }

    public function toggleStatus(RenewalEvent $event, EventPackage $package)
    {
        $package->update(['is_active' => !$package->is_active]);
        return back()->with('success', 'Package status updated.');
    }
}
