<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Facility::with('category')
            ->withCount('items');
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $facilities = $query->paginate(3);
        $categories = FacilityCategory::all();
        $highlightFacility = $request->highlight;
        
        return view('admin.facilities-index', compact('facilities', 'categories', 'highlightFacility'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = FacilityCategory::all();
        return view('admin.facilities-create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'category_id' => 'required|exists:facility_category,id',
        ]);

        Facility::create($validated);

        return redirect()->route('facilities.index')
            ->with('success', 'Facility created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Facility $facility)
    {
        // $facility->load('items', 'category');
        // return view('admin.facilities-show', compact('facility'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Facility $facility)
    {
        $categories = FacilityCategory::all();
        return view('admin.facilities-edit', compact('facility', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'category_id' => 'required|exists:facility_category,id',
        ]);

        $facility->update($validated);

        return redirect()->route('facilities.index')
            ->with('success', 'Facility updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facility $facility)
    {
        // Check if there are related facility items
        if ($facility->items()->count() > 0) {
            return redirect()->route('facilities.index')
                ->with('error', 'Cannot delete facility with related items.');
        }

        $facility->delete();

        return redirect()->route('facilities.index')
            ->with('success', 'Facility deleted successfully.');
    }

    public function destroyToo(Facility $facility)
    {
        // Check if there are related facility items
        if ($facility->items()->count() > 0) {
            return redirect()->route('facility-categories.show')
                ->with('error', 'Cannot delete facility with related items.');
        }

        $facility->delete();

        return redirect()->route('facility-categories.show')
            ->with('success', 'Facility deleted successfully.');
    }
}