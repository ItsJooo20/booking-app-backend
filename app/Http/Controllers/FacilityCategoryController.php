<?php

namespace App\Http\Controllers;

use App\Models\FacilityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FacilityCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = FacilityCategory::withCount('facilities')->paginate(6);
        
        // Pass the highlighted category ID to the view
        $highlightCategory = $request->highlight;
        
        return view('admin.facility-category-index', compact('categories', 'highlightCategory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.facility-category-create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:facility_category,name|max:50',
            'description' => 'nullable',
        ]);

        FacilityCategory::create($validated);

        return redirect()->route('facility-categories.index')
            ->with('success', 'Facility category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FacilityCategory $facilityCategory)
    {
        // $facilityCategory->load('facilities.items');
        // return view('admin.facilities-category-show', compact('facilityCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FacilityCategory $facilityCategory)
    {
        return view('admin.facility-category-edit', compact('facilityCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FacilityCategory $facilityCategory)
    {
        $validated = $request->validate([
            'name' => 'required|max:50|unique:facility_category,name,' . $facilityCategory->id,
            'description' => 'nullable',
        ]);

        $facilityCategory->update($validated);

        return redirect()->route('facility-categories.index')
            ->with('success', 'Facility category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FacilityCategory $facilityCategory)
    {
        // Check if there are related facilities
        if ($facilityCategory->facilities()->count() > 0) {
            return redirect()->route('facility-categories.index')
                ->with('error', 'Cannot delete category with related facilities.');
        }

        $facilityCategory->delete();

        return redirect()->route('facility-categories.index')
            ->with('success', 'Facility category deleted successfully.');
    }
}