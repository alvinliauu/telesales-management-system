<?php

namespace App\Http\Controllers;

use App\Models\CarType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CarTypeController extends Controller
{
    public function index()
    {
        $carTypes = CarType::ordered()->paginate(20);
        return view('car-types.index', compact('carTypes'));
    }

    public function create()
    {
        return view('car-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:car_types,code',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        CarType::create([
            'name' => $request->name,
            'code' => $request->code ?: Str::slug($request->name, '_'),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('car-types.index')
            ->with('success', 'Car type created successfully.');
    }

    public function edit(CarType $carType)
    {
        return view('car-types.edit', compact('carType'));
    }

    public function update(Request $request, CarType $carType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:car_types,code,' . $carType->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $carType->update([
            'name' => $request->name,
            'code' => $request->code ?: Str::slug($request->name, '_'),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('car-types.index')
            ->with('success', 'Car type updated successfully.');
    }

    public function destroy(CarType $carType)
    {
        if ($carType->eventPackageExtensions()->count() > 0) {
            return back()->with('error', 'Cannot delete car type that is used in packages.');
        }

        $carType->delete();

        return redirect()->route('car-types.index')
            ->with('success', 'Car type deleted successfully.');
    }

    public function toggleStatus(CarType $carType)
    {
        $carType->update(['is_active' => !$carType->is_active]);
        return back()->with('success', 'Car type status updated.');
    }
}
