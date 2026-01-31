<?php

namespace App\Http\Controllers;

use App\Models\Extension;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExtensionController extends Controller
{
    public function index()
    {
        $extensions = Extension::ordered()->paginate(20);
        return view('extensions.index', compact('extensions'));
    }

    public function create()
    {
        return view('extensions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:extensions,code',
            'description' => 'nullable|string|max:1000',
            'is_main_coverage' => 'boolean',
            'max_vehicle_age' => 'nullable|integer|min:1|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        Extension::create([
            'name' => $request->name,
            'code' => $request->code ?: Str::slug($request->name, '_'),
            'description' => $request->description,
            'is_main_coverage' => $request->boolean('is_main_coverage', false),
            'max_vehicle_age' => $request->max_vehicle_age,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('extensions.index')
            ->with('success', 'Extension created successfully.');
    }

    public function edit(Extension $extension)
    {
        return view('extensions.edit', compact('extension'));
    }

    public function update(Request $request, Extension $extension)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:extensions,code,' . $extension->id,
            'description' => 'nullable|string|max:1000',
            'is_main_coverage' => 'boolean',
            'max_vehicle_age' => 'nullable|integer|min:1|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $extension->update([
            'name' => $request->name,
            'code' => $request->code ?: Str::slug($request->name, '_'),
            'description' => $request->description,
            'is_main_coverage' => $request->boolean('is_main_coverage', false),
            'max_vehicle_age' => $request->max_vehicle_age,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('extensions.index')
            ->with('success', 'Extension updated successfully.');
    }

    public function destroy(Extension $extension)
    {
        if ($extension->eventPackageExtensions()->count() > 0) {
            return back()->with('error', 'Cannot delete extension that is used in packages.');
        }

        $extension->delete();

        return redirect()->route('extensions.index')
            ->with('success', 'Extension deleted successfully.');
    }

    public function toggleStatus(Extension $extension)
    {
        $extension->update(['is_active' => !$extension->is_active]);
        return back()->with('success', 'Extension status updated.');
    }
}
