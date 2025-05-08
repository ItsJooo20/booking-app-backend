<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\FacilityItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $bookings = Booking::with(['user', 'facilityItem.facility'])
            ->when($user->role != 'admin' && $user->role != 'headmaster', function($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->orderBy('start_datetime', 'asc')
            ->get();
            
        return response()->json([
            'status' => true,
            'data' => $bookings
        ]);
    }

    public function upcoming(Request $request)
    {
        $user = $request->user();
        
        $upcomingBookings = Booking::with(['user', 'facilityItem'])
            ->where('start_datetime', '>=', Carbon::now())
            ->when($user->role != 'admin' && $user->role != 'headmaster', function($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->orderBy('start_datetime', 'asc')
            ->get();
            
        return response()->json([
            'status' => true,
            'data' => $upcomingBookings
        ]);
    }

    public function approvedEvents(Request $request)
    {
        $validated = $request->validate([
            'facility_item_id' => 'sometimes|nullable|exists:facility_items,id',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
        ]);

        $query = Booking::with(['facilityItem.facility.category', 'user'])
            ->where('status', 'approved')
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime');

        if ($request->has('facility_item_id')) {
            $query->where('facility_item_id', $validated['facility_item_id']);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_datetime', [
                Carbon::parse($validated['start_date'])->startOfDay(),
                Carbon::parse($validated['end_date'])->endOfDay()
            ]);
        }

        $events = $query->get()->map(function($event) {
            return [
                'id' => $event->id,
                'facility_item_id' => $event->facility_item_id,
                'start_datetime' => $event->start_datetime->format('Y-m-d H:i:s'),
                'end_datetime' => $event->end_datetime->format('Y-m-d H:i:s'),
                'start_date' => $event->start_datetime->format('D, M j, Y'),
                'start_time' => $event->start_datetime->format('g:i A'),
                'end_time' => $event->end_datetime->format('g:i A'),
                'duration' => $event->start_datetime->diffInHours($event->end_datetime) . ' hours',
                'purpose' => $event->purpose,
                'status' => $event->status,
                'facility_item' => $event->facilityItem,
                'user' => $event->user
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $events
        ]);
    }

    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();
        
        // Check if user is authorized to view this booking
        if ($user->id != $booking->user_id && !in_array($user->role, ['admin', 'headmaster'])) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $booking->load(['user', 'facilityItem.facility']);
        
        return response()->json([
            'status' => true,
            'data' => $booking
        ]);
    }

    public function store(Request $request)
    {
        $token = $request->bearerToken(); // Ambil token dari header Authorization

        $user = Users::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Invalid token.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'facility_item_id' => 'required|exists:facility_items,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'purpose' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $isAvailable = $this->checkAvailability(
            $request->facility_item_id,
            $request->start_datetime,
            $request->end_datetime
        );

        if (!$isAvailable) {
            return response()->json([
                'status' => false,
                'message' => 'This facility is already reserved for the selected time slot.'
            ], 409);
        }

        $booking = new Booking();
        $booking->user_id = $user->id; // Ambil dari token
        $booking->facility_item_id = $request->facility_item_id;
        $booking->start_datetime = $request->start_datetime;
        $booking->end_datetime = $request->end_datetime;
        $booking->purpose = $request->purpose;
        $booking->status = 'pending';
        $booking->save();

        return response()->json([
            'status' => true,
            'message' => 'Booking request submitted successfully.',
            'data' => $booking
        ], 201);
    }



    public function update(Request $request, Booking $booking)
    {
        $user = $request->user();
        
        // Check authorization
        if ($user->id != $booking->user_id && !in_array($user->role, ['admin', 'headmaster'])) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validated = $request->validate([
            'facility_item_id' => 'sometimes|required|exists:facility_items,id',
            'start_datetime' => 'sometimes|required|date',
            'end_datetime' => 'sometimes|required|date|after:start_datetime',
            'purpose' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:pending,approved,rejected,completed,cancelled'
        ]);
        
        // Check availability if time or facility is being changed
        if ($request->has('facility_item_id') || $request->has('start_datetime') || $request->has('end_datetime')) {
            $facilityItemId = $request->has('facility_item_id') ? $validated['facility_item_id'] : $booking->facility_item_id;
            $startDateTime = $request->has('start_datetime') ? $validated['start_datetime'] : $booking->start_datetime;
            $endDateTime = $request->has('end_datetime') ? $validated['end_datetime'] : $booking->end_datetime;
            
            $isAvailable = $this->checkAvailability(
                $facilityItemId,
                $startDateTime,
                $endDateTime,
                $booking->id
            );
            
            if (!$isAvailable) {
                return response()->json([
                    'status' => false,
                    'message' => 'This facility is already reserved for the selected time slot.'
                ], 400);
            }
        }
        
        // Update booking
        if ($request->has('facility_item_id')) {
            $booking->facility_item_id = $validated['facility_item_id'];
        }
        if ($request->has('start_datetime')) {
            $booking->start_datetime = $validated['start_datetime'];
        }
        if ($request->has('end_datetime')) {
            $booking->end_datetime = $validated['end_datetime'];
        }
        if ($request->has('purpose')) {
            $booking->purpose = $validated['purpose'];
        }
        if ($request->has('status') && in_array($user->role, ['admin', 'headmaster'])) {
            $booking->status = $validated['status'];
        }
        
        $booking->save();
        
        return response()->json([
            'status' => true,
            'message' => 'Booking updated successfully.',
            'data' => $booking
        ]);
    }

    public function destroy(Request $request, Booking $booking)
    {
        $user = $request->user();
        
        // Check authorization
        if ($user->id != $booking->user_id && !in_array($user->role, ['admin', 'headmaster'])) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $booking->status = 'cancelled';
        $booking->save();
        
        return response()->json([
            'status' => true,
            'message' => 'Booking cancelled successfully.'
        ]);
    }

    public function approve(Request $request, Booking $booking)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['admin', 'headmaster'])) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($booking->status != 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Only pending bookings can be approved.'
            ], 400);
        }

        $isAvailable = $this->checkAvailability(
            $booking->facility_item_id,
            $booking->start_datetime,
            $booking->end_datetime,
            $booking->id
        );

        if (!$isAvailable) {
            return response()->json([
                'status' => false,
                'message' => 'This timeslot is no longer available.'
            ], 400);
        }

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
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Booking approved successfully. ' . $rejectedCount . ' conflicting bookings were rejected.',
            'data' => $booking
        ]);
    }

    public function reject(Request $request, Booking $booking)
    {
        $user = $request->user();
        
        if (!in_array($user->role, ['admin', 'headmaster'])) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $booking->status = 'rejected';
        $booking->save();
        
        return response()->json([
            'status' => true,
            'message' => 'Booking rejected successfully.',
            'data' => $booking
        ]);
    }

    private function checkAvailability($facilityItemId, $startDateTime, $endDateTime, $excludeBookingId = null)
    {
        $query = Booking::where('facility_item_id', $facilityItemId)
            ->whereIn('status', ['approved', 'completed'])
            ->where(function($query) use ($startDateTime, $endDateTime) {
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
        
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }
        
        return $query->count() === 0;
    }
}