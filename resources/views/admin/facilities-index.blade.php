@extends('layout.navbar')

@section('title', 'Facilities')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Facilities</h1>
        <div>
            <a href="{{ route('facilities.create') }}" class="btn btn-primary me-2">
                <i class="bi bi-plus-lg me-1"></i> Add Facility
            </a>
            <a href="{{ route('facility-categories.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Back to Categories
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
                    <label for="category_id" class="form-label">Filter by Category</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
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
                    <a href="{{ route('facilities.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($facilities->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-building text-muted mb-3" style="font-size: 2rem;"></i>
                <p class="text-muted mb-0">No facilities found</p>
                <p class="text-muted">Add your first facility to get started</p>
                <a href="{{ route('facilities.create') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i> Add Facility
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($facilities as $facility)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 position-relative shadow-sm">
                        <a href="{{ route('facility-items.index', ['facility_id' => $facility->id]) }}" class="stretched-link" style="z-index: 1;"></a>
                        
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                            <i class="bi bi-door-open display-4 text-muted"></i>
                        </div>
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title mb-3">{{ $facility->name }}</h5>
                                <span class="badge bg-primary rounded-pill">{{ $facility->total_items }} Items</span>
                            </div>
                            <div class="mb-2">
                                <span class="text-muted">Category:</span>
                                <span class="fw-semibold">{{ $facility->category->name }}</span>
                            </div>
                            @if($facility->description)
                                <p class="card-text text-muted">
                                    {{ Str::limit($facility->description, 80) }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-transparent border-top-0 position-relative" style="z-index: 2;">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('facilities.edit', $facility) }}" 
                                   class="btn btn-sm btn-outline-warning" 
                                   title="Edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('facilities.destroy', $facility) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this facility?');">
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
        
        @if($facilities->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $facilities->appends(['category_id' => request('category_id')])->links() }}
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