<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\FacilityItem;
use Illuminate\Http\Request;

class FacilityItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FacilityItem::with('facility.category');
        
        // Filter by facility if provided
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }
        
        $facilityItems = $query->paginate(3); // Apply pagination to the filtered query
        $facilities = Facility::all();
        
        return view('admin.facility-items-index', compact('facilityItems', 'facilities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $facilities = Facility::with('category')->get();
        return view('admin.facility-items-create', compact('facilities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'item_code' => 'required|unique:facility_items,item_code|max:50',
            // 'status' => 'required|in:available,booked,under_maintenance',
            'notes' => 'nullable',
        ]);

        FacilityItem::create($validated);

        // Update the facility counts
        $this->updateFacilityCounts($validated['facility_id']);

        return redirect()->route('facility-items.index')
            ->with('success', 'Facility item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FacilityItem $facilityItem)
    {
        // $facilityItem->load('facility.category');
        // return view('admin.dashboard', compact('facilityItem'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FacilityItem $facilityItem)
    {
        $facilities = Facility::with('category')->get();
        return view('admin.facility-items-edit', compact('facilityItem', 'facilities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FacilityItem $facilityItem)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'item_code' => 'required|max:50|unique:facility_items,item_code,' . $facilityItem->id,
            'status' => 'required|in:available,booked,under_maintenance',
            'notes' => 'nullable',
        ]);

        $oldFacilityId = $facilityItem->facility_id;
        $facilityItem->update($validated);

        // Update counts for both old and new facility if different
        $this->updateFacilityCounts($oldFacilityId);
        if ($oldFacilityId != $validated['facility_id']) {
            $this->updateFacilityCounts($validated['facility_id']);
        }

        return redirect()->route('facility-items.index')
            ->with('success', 'Facility item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FacilityItem $facilityItem)
    {
        // Check if this item is referenced in bookings
        if ($facilityItem->bookings()->count() > 0) {
            return redirect()->route('facility-items.index')
                ->with('error', 'Cannot delete facility item with related bookings.');
        }

        $facilityId = $facilityItem->facility_id;
        $facilityItem->delete();
        
        // Update the facility counts
        $this->updateFacilityCounts($facilityId);

        return redirect()->route('facility-items.index')
            ->with('success', 'Facility item deleted successfully.');
    }

    /**
     * Helper method to update the total and available item counts for a facility
     */
    private function updateFacilityCounts($facilityId)
    {
        $facility = Facility::findOrFail($facilityId);
        $totalItems = $facility->items()->count();
        $availableItems = $facility->items()->count();
        
        $facility->update([
            'total_items' => $totalItems,
            'available_items' => $availableItems
        ]);
    }
}