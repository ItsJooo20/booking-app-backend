<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Booking;
use Carbon\Carbon;

class UpdateBookingStatus
{
    public function handle($request, Closure $next)
    {
        // Update expired approved bookings to completed
        Booking::where('status', 'approved')
            ->where('end_datetime', '<=', Carbon::now())
            ->update(['status' => 'completed']);

        // Update expired pending bookings to cancelled
        Booking::where('status', 'pending')
            ->where('end_datetime', '<=', Carbon::now())
            ->update(['status' => 'cancelled']);

        return $next($request);
    }
}
