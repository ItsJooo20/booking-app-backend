@extends('layout.navbar')

@section('title', 'Report Results')

@section('content')
<div class="container-fluid py-4">

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Report Results ({{ $dateText }})</h5>
            <a href="{{ route('reports.download') }}?{{ http_build_query($filters) }}" 
               class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </a>
        </div>
        <div class="card-body">
            <!-- Booking Results Table -->
            <div class="table-responsive mb-4">
                <table class="table table-sm table-hover">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Facility Item</th>
                                        <th>User</th>
                                        <th>Date/Time</th>
                                        <th>Status</th>
                                        <th>Purpose</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bookings as $booking)
                                    <tr>
                                        <td>{{ $booking->id }}</td>
                                        <td>{{ $booking->facilityItem->item_code }}</td>
                                        <td>{{ $booking->user->name }}</td>
                                        <td>
                                            {{ $booking->start_datetime->format('M d, Y g:i A') }}<br>
                                            to {{ $booking->end_datetime->format('g:i A') }}
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($booking->status === 'approved') bg-success
                                                @elseif($booking->status === 'pending') bg-warning text-dark
                                                @elseif($booking->status === 'rejected') bg-danger
                                                @elseif($booking->status === 'completed') bg-primary
                                                @elseif($booking->status === 'cancelled') bg-secondary
                                                @endif">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($booking->purpose, 50) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No bookings match your criteria</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- ... (keep your existing table structure) ... -->
                </table>
            </div>
    
            <!-- Statistics Section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0">Booking Statistics</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    Total Bookings
                                    <span class="badge bg-primary rounded-pill">{{ $bookings->where('status', 'completed')->count() }}</span>
                                </li>
                                {{-- <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    Approved
                                    <span class="badge bg-success rounded-pill">
                                        {{ $bookings->where('status', 'completed')->count() }}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    Pending
                                    <span class="badge bg-warning rounded-pill">
                                        {{ $bookings->where('status', 'pending')->count() }}
                                    </span>
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0">Most Active Times</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="bookingHoursChart" height="150"></canvas>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
    
    @section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Booking Hours Chart
        const hoursCtx = document.getElementById('bookingHoursChart').getContext('2d');
        const hoursData = {!! json_encode($bookings->groupBy(function($item) {
            return $item->start_datetime->format('H:00');
        })->map->count()) !!};
        
        new Chart(hoursCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(hoursData),
                datasets: [{
                    label: 'Bookings per hour',
                    data: Object.values(hoursData),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
    </script>
    @endsection
{{-- 
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top 5 Most Requested Items</h5>
                </div>
                <div class="card-body"> --}}
                    {{-- @if($mostRequested->isNotEmpty())
                    <div class="list-group">
                        @foreach($mostRequested as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $item->item_code }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $item->bookings_count }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">No data available</p>
                    @endif --}}
                {{-- </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top 5 Most Booked Facilities</h5>
                </div>
                <div class="card-body"> --}}
                    {{-- @if($mostBooked->isNotEmpty())
                    <div class="list-group">
                        @foreach($mostBooked as $facility)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $facility->name }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $facility->bookings_count }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">No data available</p>
                    @endif --}}
                {{-- </div>
            </div>
        </div>
    </div> --}}
</div>
@endsection