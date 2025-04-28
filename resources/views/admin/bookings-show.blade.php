@extends('layout.navbar')

@section('title', 'Booking Details')

@section('styles')
<style>
    .booking-info-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    .info-label {
        font-weight: 500;
        color: var(--medium-gray);
    }
    .status-badge-large {
        font-size: 0.9rem;
        padding: 0.35rem 0.75rem;
    }
    .action-btn {
        min-width: 120px;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -30px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: var(--primary-color);
        z-index: 2;
    }
    .timeline-item::after {
        content: '';
        position: absolute;
        left: -25px;
        top: 12px;
        width: 2px;
        height: calc(100% - 12px);
        background-color: #dadce0;
        z-index: 1;
    }
    .timeline-item:last-child::after {
        display: none;
    }
    .timeline-content {
        padding: 0.5rem 0;
    }
    .timeline-date {
        font-size: 0.8rem;
        color: var(--medium-gray);
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Booking Details</h1>
        <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bookings
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Main Booking Info -->
            <div class="card booking-info-card mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Booking Information
                    </h5>
                    <span class="badge {{ $booking->statusBadge }} status-badge-large">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Facility</div>
                            <div class="h5">{{ $booking->facilityItem->facility->name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Facility Item</div>
                            <div class="h5">{{ $booking->facilityItem->item_code }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Start Time</div>
                            <div>{{ $booking->start_datetime->format('M d, Y g:i A') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">End Time</div>
                            <div>{{ $booking->end_datetime->format('M d, Y g:i A') }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Duration</div>
                            <div>{{ $booking->duration }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-label">Created At</div>
                            <div>{{ $booking->created_at->format('M d, Y g:i A') }}</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="info-label">Purpose</div>
                        <div>{{ $booking->purpose }}</div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
                        <!-- Edit button for owner or admin -->
                        @if(Auth::id() == $booking->user_id || in_array(Auth::user()->role, ['admin', 'headmaster']))
                            @if($booking->status != 'cancelled' && $booking->status != 'completed')
                                <a href="{{ route('bookings.edit', $booking->id) }}" class="btn btn-outline-secondary action-btn">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            @endif
                        @endif
                        
                        <!-- Cancel button for owner or admin -->
                        @if(Auth::id() == $booking->user_id || in_array(Auth::user()->role, ['admin', 'headmaster']))
                            @if($booking->status != 'cancelled' && $booking->status != 'completed')
                                <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger action-btn">
                                        <i class="bi bi-x-circle me-1"></i> Cancel
                                    </button>
                                </form>
                            @endif
                        @endif
                        
                        <!-- Approve/Reject buttons for admin -->
                        @if(in_array(Auth::user()->role, ['admin', 'headmaster']) && $booking->status == 'pending')
                            <form action="{{ route('bookings.reject', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this booking?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger action-btn">
                                    <i class="bi bi-x-circle me-1"></i> Reject
                                </button>
                            </form>
                            
                            <form action="{{ route('bookings.approve', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success action-btn">
                                    <i class="bi bi-check-circle me-1"></i> Approve
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="card booking-info-card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Booking Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">Booking Created</h6>
                                <p class="text-muted mb-0">
                                    {{ $booking->user->name }} created this booking.
                                </p>
                                <div class="timeline-date">
                                    {{ $booking->created_at->format('M d, Y g:i A') }}
                                </div>
                            </div>
                        </div>
                        
                        @if($booking->status != 'pending')
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">Booking {{ ucfirst($booking->status) }}</h6>
                                <p class="text-muted mb-0">
                                    The booking was {{ $booking->status }}.
                                </p>
                                <div class="timeline-date">
                                    {{ $booking->updated_at->format('M d, Y g:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($booking->status == 'approved' && $booking->isUpcoming)
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">Upcoming</h6>
                                <p class="text-muted mb-0">
                                    This booking is scheduled to start soon.
                                </p>
                                <div class="timeline-date">
                                    {{ $booking->start_datetime->format('M d, Y g:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($booking->isOngoing)
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">In Progress</h6>
                                <p class="text-muted mb-0">
                                    This booking is currently active.
                                </p>
                                <div class="timeline-date">
                                    Ends {{ $booking->end_datetime->format('M d, Y g:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($booking->isPast)
                        <div class="timeline-item">
                            <div class="timeline-content">
                                <h6 class="mb-1">Completed</h6>
                                <p class="text-muted mb-0">
                                    This booking has been completed.
                                </p>
                                <div class="timeline-date">
                                    {{ $booking->end_datetime->format('M d, Y g:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- User Information -->
            <div class="card booking-info-card mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>
                        User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar me-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-fill" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $booking->user->name }}</h6>
                            <div class="text-muted small">{{ $booking->user->role }}</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="info-label">Email</div>
                        <div>{{ $booking->user->email }}</div>
                    </div>
                    
                    @if($booking->user->phone)
                    <div class="mb-3">
                        <div class="info-label">Phone</div>
                        <div>{{ $booking->user->phone }}</div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Facility Details -->
            <div class="card booking-info-card">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-building me-2"></i>
                        Facility Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="info-label">Facility Name</div>
                        <div>{{ $booking->facilityItem->facility->name }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="info-label">Category</div>
                        <div>{{ $booking->facilityItem->facility->category->name }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="info-label">Item Code</div>
                        <div>{{ $booking->facilityItem->item_code }}</div>
                    </div>
                    
                    @if($booking->facilityItem->notes)
                    <div class="mb-3">
                        <div class="info-label">Notes</div>
                        <div>{{ $booking->facilityItem->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection