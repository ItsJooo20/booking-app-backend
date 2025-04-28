@extends('layout.navbar-calendar')

@section('title', 'Bookings')

@section('styles')
<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.css' rel='stylesheet' />
<style>
    .fc .fc-button-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    .fc .fc-button-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }
    .fc-event {
        border-radius: 6px;
        padding: 2px 4px;
        border: none;
    }
    .booking-card {
        border-left: 4px solid var(--primary-color);
        transition: all 0.2s;
        margin-bottom: 1rem;
    }
    .booking-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    #bookingCalendar {
        min-height: 600px;
    }
    .booking-item {
        transition: background-color 0.15s;
    }
    .booking-item:hover {
        background-color: #f8f9fa;
    }
    .btn-group-sm .btn {
        line-height: 1;
        padding-top: 0.15rem;
        padding-bottom: 0.15rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Facility Bookings</h1>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Booking
        </a>
    </div>

    <div class="row">
        @forelse($upcomingBookings as $booking)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <!-- Card Header with Item Code and Status -->
                <div class="card-header bg-white border-bottom-0 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $booking->facilityItem->item_code }}</h5>
                    <span class="badge 
                        @if($booking->status === 'approved') bg-success
                        @elseif($booking->status === 'pending') bg-warning text-dark
                        @elseif($booking->status === 'rejected') bg-danger
                        @elseif($booking->status === 'completed') bg-primary
                        @elseif($booking->status === 'cancelled') bg-secondary
                        @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                
                <!-- Card Body with Details -->
                <div class="card-body pt-0">
                    <div class="mb-2">
                        <i class="bi bi-clock text-muted me-1"></i>
                        <small class="text-muted">
                            {{ $booking->start_datetime->format('M d, Y g:i A') }} - 
                            {{ $booking->end_datetime->format('M d, Y g:i A') }}
                        </small>
                    </div>
                    
                    <p class="card-text mb-2">
                        {{ \Illuminate\Support\Str::limit($booking->purpose, 100) }}
                    </p>
                    
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-person me-1"></i>
                        {{ $booking->user->name }}
                    </div>
                </div>
                
                <!-- Card Footer with Actions -->
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('bookings.show', $booking->id) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <div class="d-flex gap-2">
                            @if(Auth::id() == $booking->user_id || in_array(Auth::user()->role, ['admin', 'headmaster']))
                                @if($booking->status != 'cancelled' && $booking->status != 'completed' && $booking->status != 'rejected')
                                <a href="{{ route('bookings.edit', $booking->id) }}" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" 
                                      onsubmit="return confirm('Cancel this booking?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No bookings found</h5>
                <a href="{{ route('bookings.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle me-1"></i> Create Booking
                </a>
            </div>
        </div>
        @endforelse
    </div>
    
    @if($upcomingBookings->hasPages())
    <div class="mt-4">
        {{ $upcomingBookings->onEachSide(1)->links() }}
    </div>
    @endif

    <!-- Calendar Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i> Calendar View</h5>
        </div>
        <div class="card-body">
            <div id="bookingCalendar"></div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card shadow-sm">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <div class="d-flex align-items-center">
                    <span class="badge bg-warning me-1">□</span>
                    <span class="small">Pending</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success me-1">□</span>
                    <span class="small">Approved</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-danger me-1">□</span>
                    <span class="small">Rejected</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-1">□</span>
                    <span class="small">Completed</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-secondary me-1">□</span>
                    <span class="small">Cancelled</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('bookingCalendar');
    
    if (calendarEl) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: @json($calendarBookings),
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            height: 'auto',
            themeSystem: 'bootstrap5',
            nowIndicator: true,
            navLinks: true,
            dayMaxEvents: true,
        });
        
        calendar.render();
    }
});
</script>
@endsection