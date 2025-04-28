@extends('layout.navbar')

@section('title', 'Booking Reports')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Reports</h1>
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-file-earmark-bar-graph me-2"></i>Generate Report</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('reports.generate') }}" method="GET">
                <!-- Report Period Selection -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Report Period</label>
                        <select class="form-select" name="date_range_type" id="dateRangeType" required>
                            <option value="day" selected>Single Day</option>
                            <option value="range">Date Range</option>
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                        </select>
                    </div>
                </div>

                <!-- Date Fields - Will Show Based on Selection -->
                <div id="dayFields" class="date-input-group">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Select Date</label>
                            <input type="date" class="form-control" name="date_day" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div id="rangeFields" class="date-input-group" style="display:none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="date_range_start" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="date_range_end" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div id="monthFields" class="date-input-group" style="display:none;">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Select Month</label>
                            <input type="month" class="form-control" name="date_month" value="{{ date('Y-m') }}">
                        </div>
                    </div>
                </div>

                <div id="yearFields" class="date-input-group" style="display:none;">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Select Year</label>
                            <input type="number" class="form-control" name="date_year" min="2000" max="2100" value="{{ date('Y') }}">
                        </div>
                    </div>
                </div>
        
                <!-- Other filters (status, user, facility, category) -->
                <div class="row mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">User</label>
                        <select class="form-select" name="user_id">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                        <label class="form-label">Facility Category</label>
                        <select class="form-select" name="category_id" id="categorySelect">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Facility</label>
                        <select class="form-select" name="facility_id" id="facilitySelect">
                            <option value="">All Facilities</option>
                            @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }} ({{ $facility->category->name }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                <label for="facility_item_id" class="form-label">Facility Item</label>
                        <select class="form-select" id="facilityItemsSelect" name="facility_item_id">
                            <option value="">All Item</option>
                            @foreach($facilityItems as $item)
                            <option value="{{ $item->id }}" data-facility="{{ $item->facility_id }}">
                                {{ $item->item_code }} ({{ $item->facility->name }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
        
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-filter me-1"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inline JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Date range selector elements
        const dateRangeType = document.getElementById('dateRangeType');
        const dayFields = document.getElementById('dayFields');
        const rangeFields = document.getElementById('rangeFields');
        const monthFields = document.getElementById('monthFields');
        const yearFields = document.getElementById('yearFields');
    
        // Function to update visible date fields
        function updateDateFields() {
            dayFields.style.display = 'none';
            rangeFields.style.display = 'none';
            monthFields.style.display = 'none';
            yearFields.style.display = 'none';
    
            const selectedValue = dateRangeType.value;
            if (selectedValue === 'day') {
                dayFields.style.display = 'block';
            } else if (selectedValue === 'range') {
                rangeFields.style.display = 'block';
            } else if (selectedValue === 'month') {
                monthFields.style.display = 'block';
            } else if (selectedValue === 'year') {
                yearFields.style.display = 'block';
            }
        }
    
        // Initialize date fields if present
        if (dateRangeType) {
            updateDateFields();
            dateRangeType.addEventListener('change', updateDateFields);
        }
    
        // Category and facility selector elements
        const categorySelect = document.getElementById('categorySelect');
        const facilitySelect = document.getElementById('facilitySelect');
        const facilityItemsSelect = document.getElementById('facilityItemsSelect');
    
        if (categorySelect && facilitySelect) {
            // When category is selected, disable facility dropdown
            categorySelect.addEventListener('change', function () {
                if (this.value !== "") {
                    facilitySelect.disabled = true;
                    facilitySelect.value = ""; // Reset facility selection
                    facilityItemsSelect.disabled = true;
                    facilityItemsSelect.value = ""; // Reset category selection
                } else {
                    facilitySelect.disabled = false;
                    facilityItemsSelect.disabled = false;
                }
            });
    
            // When facility is selected, disable category dropdown
            facilitySelect.addEventListener('change', function () {
                if (this.value !== "") {
                    categorySelect.disabled = true;
                    categorySelect.value = ""; // Reset category selection
                    facilityItemsSelect.disabled = true;
                    facilityItemsSelect.value = ""; // Reset category selection
                } else {
                    categorySelect.disabled = false;
                    facilityItemsSelect.disabled = false;
                }
            });

            facilityItemsSelect.addEventListener('change', function () {
                if (this.value !== "") {
                    categorySelect.disabled = true;
                    categorySelect.value = ""; // Reset category selection
                    facilitySelect.disabled = true;
                    facilitySelect.value = ""; // Reset facility selection
                } else {
                    categorySelect.disabled = false;
                    facilitySelect.disabled = false;
                }
            });
        }
    });
    </script>
    
@endsection