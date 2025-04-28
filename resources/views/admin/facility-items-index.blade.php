@extends('layout.navbar')

@section('title', 'Facility Items')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Facility Items</h1>
        <div>
            <a href="{{ route('facility-items.create') }}" class="btn btn-primary me-2">
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </a>
            <a href="{{ route('facilities.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Facilities
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-end g-3">
                <div class="col-md">
                    <label for="facility_id" class="form-label">Filter by Facility</label>
                    <select class="form-select" id="facility_id" name="facility_id">
                        <option value="">All Facilities</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}" {{ request('facility_id') == $facility->id ? 'selected' : '' }}>
                                {{ $facility->name }} ({{ $facility->category->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-auto">
                    <a href="{{ route('facility-items.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($facilityItems->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-box-seam text-muted mb-3" style="font-size: 2rem;"></i>
                <p class="text-muted mb-0">No facility items found</p>
                <p class="text-muted">Add your first item to get started</p>
                <a href="{{ route('facility-items.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i> Add Item
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($facilityItems as $item)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <!-- Item Image Placeholder -->
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                            <i class="bi bi-box-seam display-4 text-muted"></i>
                        </div>
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">{{ $item->item_code }}</h5>
                            </div>
                            
                            <div class="mb-2">
                                <span class="text-muted">Facility:</span>
                                <span class="fw-semibold">{{ $item->facility->name }}</span>
                            </div>
                            
                            <div class="mb-2">
                                <span class="text-muted">Category:</span>
                                <span class="fw-semibold">{{ $item->facility->category->name }}</span>
                            </div>
                        </div>
                        
                        <!-- Action buttons -->
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('facility-items.edit', $item) }}" 
                                   class="btn btn-sm btn-outline-warning" 
                                   title="Edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('facility-items.destroy', $item) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if($facilityItems->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $facilityItems->links() }}
        </div>
        @endif
    @endif
</div>

<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .card-img-top {
        transition: opacity 0.2s;
    }
    .card:hover .card-img-top {
        opacity: 0.9;
    }
    .fw-semibold {
        font-weight: 600;
    }
</style>
@endsection