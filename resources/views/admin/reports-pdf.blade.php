<!DOCTYPE html>
<html>
<head>
    <title>Booking Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .badge { padding: 3px 6px; border-radius: 3px; font-size: 12px; }
        .section { margin-bottom: 30px; }
        .section-title { border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Booking Report</h1>
        <p>Generated on: {{ now()->format('M d, Y g:i A') }}</p>
    </div>

    <div class="section">
        <h3 class="section-title">Booking Details</h3>
        <table>
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
                        <span style="background-color: 
                            @if($booking->status === 'approved') #28a745
                            @elseif($booking->status === 'pending') #ffc107
                            @elseif($booking->status === 'rejected') #dc3545
                            @elseif($booking->status === 'completed') #007bff
                            @elseif($booking->status === 'cancelled') #6c757d
                            @endif; 
                            color: @if($booking->status === 'pending') #000 @else #fff @endif;
                            padding: 2px 5px; border-radius: 3px;">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($booking->purpose, 50) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No bookings match your criteria</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row" style="display: flex; margin-bottom: 20px;">
        <div class="section" style="flex: 1; margin-right: 10px;">
            <h3 class="section-title">Top 5 Most Requested Items</h3>
            {{-- @if($mostRequested->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mostRequested as $item)
                    <tr>
                        <td>{{ $item->item_code }}</td>
                        <td>{{ $item->bookings_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>No data available</p>
            @endif --}}
        </div>

        <div class="section" style="flex: 1; margin-left: 10px;">
            <h3 class="section-title">Top 5 Most Booked Facilities</h3>
            {{-- @if($mostBooked->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>Facility</th>
                        <th>Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mostBooked as $facility)
                    <tr>
                        <td>{{ $facility->name }}</td>
                        <td>{{ $facility->bookings_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>No data available</p>
            @endif --}}
        </div>
    </div>
</body>
</html>