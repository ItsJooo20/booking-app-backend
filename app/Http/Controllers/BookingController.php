<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\FacilityItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BookingController extends Controller
{
    use ValidatesRequests;
    /**
     * Display a listing of the bookings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings = Booking::with(['user', 'facilityItem.facility'])
            ->orderBy('start_datetime', 'asc')
            ->get();
        
        // Format data for calendar
        $calendarBookings = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => $booking->facilityItem->item_code . ' - ' . $booking->purpose,
                'start' => $booking->start_datetime->format('Y-m-d\TH:i:s'),
                'end' => $booking->end_datetime->format('Y-m-d\TH:i:s'),
                'color' => $this->getStatusColor($booking->status),
                'url' => route('bookings.show', $booking->id),
            ];
        });
        
        // Get upcoming bookings for sidebar
        $upcomingBookings = Booking::with(['user', 'facilityItem'])
        ->where('start_datetime', '>=', Carbon::now())
        ->where(function($query) {
            if (!in_array(Auth::user()->role, ['admin', 'headmaster'])) {
                $query->where('user_id', Auth::id());
            }
        })
        ->orderBy('start_datetime', 'asc')
        ->paginate(3); // Changed from get() to paginate(5)
        
        return view('admin.bookings-index', compact('bookings', 'calendarBookings', 'upcomingBookings'));
    }

    /**
     * Show the form for creating a new booking.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $facilities = Facility::with('category')->get();
        $facilityItems = FacilityItem::with('facility.category')->get();
        
        return view('admin.bookings-create', compact('facilities', 'facilityItems'));
    }

    /**
     * Store a newly created booking in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'facility_item_id' => 'required|exists:facility_items,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string|max:255',
        ]);
        
        // Check if the facility item is available for the selected time
        $isAvailable = $this->checkAvailability(
            $request->facility_item_id,
            $request->start_datetime,
            $request->end_datetime
        );
        
        if (!$isAvailable) {
            return redirect()->back()
                ->with('error', 'This facility is already reserved for the selected time slot.')
                ->withInput();
        }
        
        $booking = new Booking();
        $booking->user_id = Auth::id();
        $booking->facility_item_id = $request->facility_item_id;
        $booking->start_datetime = $request->start_datetime;
        $booking->end_datetime = $request->end_datetime;
        $booking->purpose = $request->purpose;
        $booking->status = 'pending';
        $booking->save();
        
        return redirect()->route('bookings.index')
            ->with('success', 'Booking request submitted successfully.');
    }

    /**
     * Display the specified booking.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        $booking->load(['user', 'facilityItem.facility']);
        
        return view('admin.bookings-show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        // Check if user can edit this booking
        if (Auth::id() != $booking->user_id && !in_array(Auth::user()->role, ['admin', 'headmaster'])) {
            return redirect()->route('bookings.index')->with('error', 'You are not authorized to edit this booking.');
        }
        
        $facilities = Facility::with('items')->get();
        
        return view('admin.bookings-edit', compact('booking', 'facilities'));
    }

    /**
     * Update the specified booking in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Booking $booking)
    {
        // Check if user can update this booking
        if (Auth::id() != $booking->user_id && !in_array(Auth::user()->role, ['admin', 'headmaster'])) {
            return redirect()->route('bookings.index')->with('error', 'You are not authorized to update this booking.');
        }
        
        $this->validate($request, [
            'facility_item_id' => 'required|exists:facility_items,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string|max:255',
        ]);
        
        // Check availability excluding current booking
        $isAvailable = $this->checkAvailability(
            $request->facility_item_id,
            $request->start_datetime,
            $request->end_datetime,
            $booking->id
        );
        
        if (!$isAvailable) {
            return redirect()->back()
                ->with('error', 'This facility is already reserved for the selected time slot.')
                ->withInput();
        }
        
        $booking->facility_item_id = $request->facility_item_id;
        $booking->start_datetime = $request->start_datetime;
        $booking->end_datetime = $request->end_datetime;
        $booking->purpose = $request->purpose;
        
        // If admin is updating, they can change status
        if (in_array(Auth::user()->role, ['admin', 'headmaster']) && $request->has('status')) {
            $booking->status = $request->status;
        } else {
            // If user updates their own booking, reset status to pending
            $booking->status = 'pending';
        }
        
        $booking->save();
        
        return redirect()->route('bookings.index')->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified booking from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {
        // Check if user can delete this booking
        if (Auth::id() != $booking->user_id && !in_array(Auth::user()->role, ['admin', 'headmaster'])) {
            return redirect()->route('bookings.index')->with('error', 'You are not authorized to delete this booking.');
        }
        
        $booking->status = 'cancelled';
        $booking->save();
        
        return redirect()->route('bookings.index')->with('success', 'Booking cancelled successfully.');
    }
    
    /**
     * Approve a booking request
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function approve(Booking $booking)
    {
        if (!in_array(Auth::user()->role, ['admin', 'headmaster'])) {
            return redirect()->route('bookings.index')
                ->with('error', 'You are not authorized to approve bookings.');
        }

        if ($booking->status != 'pending') {
            return redirect()->route('bookings.show', $booking->id)
                ->with('error', 'Only pending bookings can be approved.');
        }

        $isAvailable = $this->checkAvailability(
            $booking->facility_item_id,
            $booking->start_datetime,
            $booking->end_datetime,
            $booking->id
        );

        if (!$isAvailable) {
            return redirect()->route('bookings.show', $booking->id)
                ->with('error', 'This timeslot is no longer available.');
        }

        // Initialize counter
        $rejectedCount = 0;

        DB::transaction(function () use ($booking, &$rejectedCount) {
            // Approve the selected booking
            $booking->status = 'approved';
            $booking->save();

            // Find and reject all conflicting pending bookings
            $conflictingBookings = Booking::where('facility_item_id', $booking->facility_item_id)
                ->where('id', '!=', $booking->id)
                ->where('status', 'pending')
                ->where(function($query) use ($booking) {
                    $query->where(function($q) use ($booking) {
                        $q->where('start_datetime', '>=', $booking->start_datetime)
                        ->where('start_datetime', '<', $booking->end_datetime);
                    })->orWhere(function($q) use ($booking) {
                        $q->where('end_datetime', '>', $booking->start_datetime)
                        ->where('end_datetime', '<=', $booking->end_datetime);
                    })->orWhere(function($q) use ($booking) {
                        $q->where('start_datetime', '<=', $booking->start_datetime)
                        ->where('end_datetime', '>=', $booking->end_datetime);
                    });
                })
                ->get();

            $rejectedCount = $conflictingBookings->count();

            foreach ($conflictingBookings as $conflict) {
                $conflict->status = 'rejected';
                $conflict->save();
                
                // Notification::send($conflict->user, new BookingRejected($conflict));
            }
        });

        return redirect()->route('bookings.show', $booking->id)
            ->with('success', 'Booking approved successfully. ' . 
                $rejectedCount . ' conflicting bookings were rejected.');
    }
    
    /**
     * Reject a booking request
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function reject(Booking $booking)
    {
        $booking->status = 'rejected';
        $booking->save();
        
        return redirect()->route('bookings.show', $booking->id)->with('success', 'Booking rejected successfully.');
    }
    
    /**
     * Check if facility item is available for the given time slot
     * 
     * @param int $facilityItemId
     * @param string $startDateTime
     * @param string $endDateTime
     * @param int|null $excludeBookingId
     * @return bool
     */
    private function checkAvailability($facilityItemId, $startDateTime, $endDateTime, $excludeBookingId = null)
    {
        $query = Booking::where('facility_item_id', $facilityItemId)
            // Only consider approved or completed bookings as blocking
            ->whereIn('status', ['approved', 'completed'])
            ->where(function($query) use ($startDateTime, $endDateTime) {
                // Check for overlapping bookings
                $query->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_datetime', '>=', $startDateTime)
                    ->where('start_datetime', '<', $endDateTime);
                })->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('end_datetime', '>', $startDateTime)
                    ->where('end_datetime', '<=', $endDateTime);
                })->orWhere(function($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_datetime', '<=', $startDateTime)
                    ->where('end_datetime', '>=', $endDateTime);
                });
            });
        
        // Exclude current booking when updating
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }
        
        $conflictingBookings = $query->count();
        
        return $conflictingBookings === 0;
    }
    
    /**
     * Get color for calendar event based on booking status
     * 
     * @param string $status
     * @return string
     */
    private function getStatusColor($status)
    {
        $colors = [
            'pending' => '#FBBC05',    // Warning yellow
            'approved' => '#34A853',   // Success green
            'rejected' => '#EA4335',   // Danger red
            'completed' => '#1A73E8',  // Primary blue
            'cancelled' => '#5F6368',  // Medium gray
        ];
        
        return $colors[$status] ?? '#1A73E8';
    }
}