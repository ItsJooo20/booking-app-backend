@extends('layout.navbar')

@section('title', 'Create Booking')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .facility-card {
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
        border-radius: 10px;
    }
    .facility-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .facility-card.selected {
        border-color: var(--primary-color);
        background-color: rgba(26, 115, 232, 0.05);
    }
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
        <h1 class="h3 mb-0">Create New Booking</h1>
        <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bookings
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-red alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="error" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warn alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="warning" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('bookings.store') }}" method="POST">
                @csrf
                
                <!-- Facility Selection -->
                <div class="form-section">
                    <h5 class="form-section-title">
                        <i class="bi bi-building me-2"></i>Select Facility
                    </h5>
                    
                    <div class="mb-3">
                        <label for="facility_id" class="form-label">Facility Category</label>
                        <select class="form-select" id="facility_id" name="facility_id" required>
                            <option value="">-- Select Facility Category --</option>
                            @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }} ({{ $facility->category->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="facility_item_id" class="form-label">Facility Item</label>
                        <select class="form-select" id="facility_item_id" name="facility_item_id" required>
                            <option value="">-- Select Facility Item --</option>
                            @foreach($facilityItems as $item)
                            <option value="{{ $item->id }}" data-facility="{{ $item->facility_id }}">
                                {{ $item->item_code }} ({{ $item->facility->name }})
                            </option>
                            @endforeach
                        </select>
                        @error('facility_item_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Booking Details -->
                <div class="form-section">
                    <h5 class="form-section-title">
                        <i class="bi bi-calendar-event me-2"></i>Booking Details
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_datetime" class="form-label">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required value="{{ old('start_datetime') }}">
                            @error('start_datetime')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="end_datetime" class="form-label">End Date & Time</label>
                            <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required value="{{ old('end_datetime') }}">
                            @error('end_datetime')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose of Booking</label>
                        <textarea class="form-control" id="purpose" name="purpose" rows="3" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Additional Notes -->
                <div class="form-section">
                    {{-- <h5 class="form-section-title">
                        <i class="bi bi-info-circle me-2"></i>Additional Information
                    </h5> --}}
                    
                    <div class="alert alert-warn">
                        <i class="bi bi-info-circle-fill me-2"></i> 
                        Your booking request will be reviewed by an administrator. You will be notified once your booking is approved.
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-outline-secondary me-2" onclick="window.location.href='{{ route('bookings.index') }}'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Booking Request</button>
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
        minDate: "today"
    });
    
    flatpickr("#end_datetime", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today"
    });
    
    // Filter facility items based on selected facility
    const facilitySelect = document.getElementById('facility_id');
    const itemSelect = document.getElementById('facility_item_id');
    
    facilitySelect.addEventListener('change', function() {
        const facilityId = this.value;
        const items = itemSelect.querySelectorAll('option');
        
        items.forEach(item => {
            if (item.value === '') {
                item.style.display = 'block'; // Always show the default option
            } else {
                const itemFacilityId = item.getAttribute('data-facility');
                item.style.display = (facilityId === '' || itemFacilityId === facilityId) ? 'block' : 'none';
            }
        });
        
        // Reset item selection when facility changes
        itemSelect.value = '';
    });
});
</script>
@endsection