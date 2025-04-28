@extends('layout.navbar')

@section('title', 'Facility Categories')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Facility Categories</h1>
        <a href="{{ route('facility-categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Add Facility Category
        </a>
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

    <div class="card shadow-sm">
        <div class="card-body">
            @if($categories->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-building mb-2" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0">No facility categories found</p>
                    <p class="text-muted">Start by adding your first facility category</p>
                    <a href="{{ route('facility-categories.create') }}" class="btn btn-primary mt-3">
                        <i class="bi bi-plus-lg me-1"></i> Add Facility Category
                    </a>
                </div>
            @else
                <div class="row">
                    @foreach($categories as $category)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 position-relative">
                                <!-- Modified clickable overlay to route to filtered facilities index -->
                                <a href="{{ route('facilities.index', ['category_id' => $category->id]) }}" class="stretched-link" style="z-index: 1;"></a>
                                
                                <!-- Icon/Picture Placeholder -->
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                    <i class="bi bi-building display-4 text-muted"></i>
                                </div>
                                
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title mb-3">{{ $category->name }}</h5>
                                        <span class="badge bg-primary rounded-pill">{{ $category->facilities_count }} Facilities</span>
                                    </div>
                                    <p class="card-text text-muted">
                                        {{ Str::limit($category->description, 100) }}
                                    </p>
                                </div>
                                
                                <!-- Action buttons with higher z-index to stay clickable -->
                                <div class="card-footer bg-transparent border-top-0 position-relative" style="z-index: 2;">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('facility-categories.edit', $category) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('facility-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
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
            @endif

            @if($categories->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
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
</style>
@endsection