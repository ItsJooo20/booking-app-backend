@extends('layout.navbar')

@section('title', 'Edit Booking')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .form-section {
        margin-bottom: 2rem;
    }
    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit Booking</h1>
        <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bookings
        </a>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('bookings.update', $booking->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Facility Selection -->
                <div class="form-section">
                    <h5 class="form-section-title">
                        <i class="bi bi-building me-2"></i>Select Facility
                    </h5>
                    
                    <div class="mb-3">
                        <label for="facility_id" class="form-label">Facility Category</label>
                        <input type="text" class="form-control" 
                               value="{{ $booking->facilityItem->facility->name }}" readonly>
                        <input type="hidden" name="facility_id" value="{{ $booking->facilityItem->facility_id }}">
                    </div>
                    
                    <div class="mb-3">
                        <label for="facility_item_id" class="form-label">Facility Item</label>
                        <input type="text" class="form-control" 
                               value="{{ $booking->facilityItem->item_code }} {{ $booking->facilityItem->notes ? '('.$booking->facilityItem->notes.')' : '' }}" readonly>
                        <input type="hidden" name="facility_item_id" value="{{ $booking->facility_item_id }}">
                    </div>
                
                <!-- Booking Details -->
                <div class="form-section">
                    <h5 class="form-section-title">
                        <i class="bi bi-calendar-event me-2"></i>Booking Details
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_datetime" class="form-label">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required 
                                value="{{ old('start_datetime', $booking->start_datetime->format('Y-m-d\TH:i')) }}">
                            @error('start_datetime')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_datetime" class="form-label">End Date & Time</label>
                            <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required 
                                value="{{ old('end_datetime', $booking->end_datetime->format('Y-m-d\TH:i')) }}">
                            @error('end_datetime')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose of Booking</label>
                        <textarea class="form-control" id="purpose" name="purpose" rows="3" required>{{ old('purpose', $booking->purpose) }}</textarea>
                        @error('purpose')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    @if(in_array(Auth::user()->role, ['admin', 'headmaster']))
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $booking->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $booking->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    @endif
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="window.location.href='{{ route('bookings.index') }}'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize datetime picker
    flatpickr("#start_datetime", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "{{ $booking->is_upcoming ? 'today' : null }}",
        time_24hr: false
    });
    
    flatpickr("#end_datetime", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "{{ $booking->is_upcoming ? 'today' : null }}",
        time_24hr: false
    });
    
    // Handle facility selection to load items
    const facilitySelect = document.getElementById('facility_id');
    const itemSelect = document.getElementById('facility_item_id');
    const currentItemId = '{{ $booking->facility_item_id }}';
    
    facilitySelect.addEventListener('change', function() {
        const facilityId = this.value;
        
        if (!facilityId) {
            itemSelect.innerHTML = '<option value="">-- Select Facility First --</option>';
            return;
        }
        
        // Find the selected facility and its items
        const facilities = @json($facilities);
        const selectedFacility = facilities.find(f => f.id == facilityId);
        
        if (selectedFacility && selectedFacility.items) {
            // Populate items dropdown
            itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
            selectedFacility.items.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.item_code + (item.notes ? ` (${item.notes})` : '');
                if (item.id == currentItemId) {
                    option.selected = true;
                }
                itemSelect.appendChild(option);
            });
        }
    });
});
</script>
@endsection