@extends('layout.navbar')

@section('title', 'Edit Facility Item')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Edit Facility Item</h2>
        <a href="{{ route('facility-items.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Back to Items
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('facility-items.update', $facilityItem) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="facility_id" class="form-label">Facility</label>
                    <select class="form-select @error('facility_id') is-invalid @enderror" id="facility_id" name="facility_id" required>
                        <option value="">Select a facility</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}" {{ old('facility_id', $facilityItem->facility_id) == $facility->id ? 'selected' : '' }}>
                                {{ $facility->name }} ({{ $facility->category->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('facility_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="item_code" class="form-label">Item Code</label>
                    <input type="text" class="form-control @error('item_code') is-invalid @enderror" id="item_code" name="item_code" value="{{ old('item_code', $facilityItem->item_code) }}" required>
                    <div class="form-text">A unique identifier for this item (e.g., MIC-001, CHAIR-042)</div>
                    @error('item_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="available" {{ old('status', $facilityItem->status) == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="booked" {{ old('status', $facilityItem->status) == 'booked' ? 'selected' : '' }}>Booked</option>
                        <option value="under_maintenance" {{ old('status', $facilityItem->status) == 'under_maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $facilityItem->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection