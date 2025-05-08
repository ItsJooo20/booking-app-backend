<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\FacilityItem;
use Illuminate\Http\Request;
use App\Models\FacilityCategory;
use App\Http\Controllers\Controller;

class FacilityApiController extends Controller
{
    public function categories(Request $request)
    {
        $categories = FacilityCategory::withCount('facilities')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get all facilities (optionally filtered by category)
     */
    public function facilities(Request $request)
    {
        $query = Facility::with(['category'])
            ->withCount('items')
            ->orderBy('name');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $facilities = $query->get();

        return response()->json([
            'status' => true,
            'data' => $facilities
        ]);
    }

    /**
     * Get all facility items (optionally filtered by facility)
     */
    public function items(Request $request)
    {
        $query = FacilityItem::with(['facility.category'])
            ->orderBy('item_code');

        if ($request->has('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->get();

        return response()->json([
            'status' => true,
            'data' => $items
        ]);
    }

    /**
     * Get details of a specific facility item
     */
    public function itemDetails(FacilityItem $item)
    {
        $item->load(['facility.category', 'bookings' => function($query) {
            $query->where('status', 'approved')
                ->where('end_datetime', '>=', now())
                ->orderBy('start_datetime');
        }]);

        return response()->json([
            'status' => true,
            'data' => $item
        ]);
    }

    /**
     * Check availability of a facility item
     */
    public function checkAvailability(Request $request, FacilityItem $item)
    {
        $validated = $request->validate([
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'exclude_booking_id' => 'sometimes|nullable|exists:bookings,id'
        ]);
        
        $query = Booking::where('facility_item_id', $item->id)
            ->whereIn('status', ['approved', 'completed'])
            ->where(function($query) use ($validated) {
                $query->where(function($q) use ($validated) {
                    $q->where('start_datetime', '>=', $validated['start_datetime'])
                    ->where('start_datetime', '<', $validated['end_datetime']);
                })->orWhere(function($q) use ($validated) {
                    $q->where('end_datetime', '>', $validated['start_datetime'])
                    ->where('end_datetime', '<=', $validated['end_datetime']);
                })->orWhere(function($q) use ($validated) {
                    $q->where('start_datetime', '<=', $validated['start_datetime'])
                    ->where('end_datetime', '>=', $validated['end_datetime']);
                });
            });
        
        if (!empty($validated['exclude_booking_id'])) {
            $query->where('id', '!=', $validated['exclude_booking_id']);
        }
        
        $isAvailable = $query->count() === 0;
        
        return response()->json([
            'status' => true,
            'data' => [
                'available' => $isAvailable,
                'item' => $item,
                'conflicts' => $isAvailable ? [] : $query->get()
            ]
        ]);
    }
}