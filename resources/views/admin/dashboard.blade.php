@extends('layout.navbar-calendar')

@section('title', 'Dashboard')

@section('styles')
<link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.css' rel='stylesheet' />
<style>
    .fc .fc-button-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #5E4A2D;
    }
    .fc .fc-button-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }
    .fc .fc-button-primary:not(:disabled).fc-button-active, 
    .fc .fc-button-primary:not(:disabled):active {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }
    .fc-event {
        border-radius: 6px;
        padding: 2px 4px;
        border: none;
    }
    .stats-card {
        transition: all 0.2s;
        border-radius: 10px;
    }
    .stats-card:hover {
        transform: translateY(-3px);
    }
    .booking-list-item {
        border-left: 4px solid var(--primary-color);
        transition: all 0.2s;
    }
    .booking-list-item:hover {
        background-color: var(--light-gray);
    }
    .usage-bar {
        height: 10px;
        border-radius: 5px;
        background-color: #EAE0CD;
        overflow: hidden;
    }
    .usage-progress {
        height: 100%;
        background-color: var(--primary-color);
    }
    #bookingCalendar {
        min-height: 600px;
        width: 100%;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Dashboard</h1>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Calendar Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event me-2"></i>
                        Facility Booking Calendar
                    </h5>
                </div>
                <div class="card-body">
                    <div id="bookingCalendar" style="min-height: 400px; display: block !important;">
                        </div>
                    <div style="display: none;">
                        {{-- <pre>{{ json_encode($calendarBookings, JSON_PRETTY_PRINT) }}</pre>  --}}
                    </div>
                </div>
            </div>
            
            {{-- @if(in_array(Auth::user()->role, ['admin', 'headmaster']))
            <!-- Facility Usage (Admin Only) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        Facility Usage
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($facilityUsage as $facility)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium">{{ $facility['name'] }}</span>
                            <span class="text-muted small">{{ $facility['used'] }}/{{ $facility['total'] }} items in use</span>
                        </div>
                        <div class="usage-bar">
                            <div class="usage-progress" style="width: {{ $facility['percentage'] }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">No facility data available</p>
                    @endforelse
                </div>
            </div>
            @endif --}}
        </div>
        
        <div class="col-lg-4">
            @if(in_array(Auth::user()->role, ['admin', 'headmaster']))
            <!-- Stats Cards (Admin Only) -->
            <div class="row mb-4">
                <div class="col-6">
                    <div class="card stats-card bg-light-gray mb-4">
                        <div class="card-body text-center py-3">
                            <h3 class="h2 text-primary mb-0">{{ $stats['total_facilities'] }}</h3>
                            <div class="text-muted small">Total Items</div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-6">
                    <div class="card stats-card bg-light-gray mb-4">
                        <div class="card-body text-center py-3">
                            <h3 class="h2 text-primary mb-0">{{ $stats['available_items'] }}</h3>
                            <div class="text-muted small">Available Items</div>
                        </div>
                    </div>
                </div> --}}
                <div class="col-6">
                    <div class="card stats-card bg-light-gray mb-4">
                        <div class="card-body text-center py-3">
                            <h3 class="h2 text-warning mb-0">{{ $stats['pending_bookings'] }}</h3>
                            <div class="text-muted small">Pending Approvals</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stats-card bg-light-gray mb-4">
                        <div class="card-body text-center py-3">
                            <h3 class="h2 text-success mb-0">{{ $stats['active_bookings'] }}</h3>
                            <div class="text-muted small">Active Bookings</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Upcoming Bookings -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-check me-2"></i>
                        Upcoming Bookings
                    </h5>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @forelse($upcomingBookings as $booking)
                    <div class="booking-list-item p-3 border-bottom">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">{{ $booking->facilityItem->item_code }}</h6>
                            <span class="badge {{ $booking->status === 'approved' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                        <div class="small text-muted mb-2">
                            {{ $booking->start_datetime->format('M d, Y g:i A') }} - 
                            {{ $booking->end_datetime->format('M d, Y g:i A') }}
                        </div>
                        <div class="small">{{ \Illuminate\Support\Str::limit($booking->purpose, 50) }}</div>
                        @if(Auth::user()->role === 'admin')
                        <div class="small text-muted mt-1">Booked by: {{ $booking->user->name }}</div>
                        @endif
                    </div>
                    @empty
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-calendar-x mb-2" style="font-size: 1.5rem;"></i>
                        <p class="mb-0">No upcoming bookings</p>
                    </div>
                    @endforelse
                </div>
                @if(count($upcomingBookings) > 0)
                <div class="card-footer bg-white text-center py-2">
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Book Facility
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/fullcalendar.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
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
</script> --}}

{{-- @section('scripts') --}}

{{-- <script>
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
                events: @json($calendarBookings), // Proper JSON encoding
                height: 'auto',
                nowIndicator: true,
                navLinks: true,
                dayMaxEvents: true
            });
            
            calendar.render();
        }
    });
    </script> --}}

@endsection