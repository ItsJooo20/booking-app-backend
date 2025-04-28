<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css' rel='stylesheet' />
    <style>
        :root {
            --primary-color: #1A73E8;
            --primary-hover: #2B7DE9;
            --secondary-color: #4285F4;
            --light-gray: #F8F9FA;    
            --light: #E8F5E9;
            --light-danger: #ffdddd;  
            --light-warn: #FEFAE0;        
            --bld: #0F9D58;    
            --dark-gray: #202124;             
            --medium-gray: #5F6368;          
            --border-color: #DADCE0;          
            --success-color: #34A853;       
            --warning-color: #FBBC05;        
            --danger-color: #EA4335;      
        }
    
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--light-gray);
            color: var(--dark-gray);
            line-height: 1.6;
        }

        body.modal-open {
            overflow-y: scroll !important;
        }
    
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 0.75rem 1rem;
        }
    
        .navbar-brand {
            font-weight: 600;
            color: var(--dark-gray);
            font-size: 1.25rem;
        }
    
        .nav-link {
            font-weight: 500;
            color: var(--medium-gray);
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.2s;
        }
    
        .nav-link:hover, 
        .nav-link:focus,
        .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(26, 115, 232, 0.08);
        }
    
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            padding: 0.5rem;
        }
    
        .dropdown-item {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.2s;
            color: var(--medium-gray);
        }
    
        .dropdown-item:hover {
            background-color: rgba(26, 115, 232, 0.08);
            color: var(--primary-color);
        }
    
        /* User info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
    
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(26, 115, 232, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
        }
    
        .user-name {
            font-weight: 500;
            color: var(--dark-gray);
        }
    
        .user-role {
            font-size: 0.75rem;
            color: var(--medium-gray);
        }
    
        /* Logout button */
        .btn-logout {
            border: none;
            background-color: rgba(234, 67, 53, 0.1);
            color: var(--danger-color);
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
            transition: all 0.2s;
        }
    
        .btn-logout:hover {
            background-color: rgba(234, 67, 53, 0.2);
        }
    
        /* Main content */
        .main-content {
            padding: 1.5rem;
            margin-top: 1rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
    
        /* Mobile optimizations */
        @media (max-width: 992px) {
            .navbar-collapse {
                padding: 1rem;
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
                margin-top: 0.5rem;
            }
            
            .dropdown-menu {
                margin-left: 1rem;
                width: calc(100% - 2rem);
            }
            
            .user-info {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(32, 33, 36, 0.08);
            }
        }
    
        /* Modern minimalist card style */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
    
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    
        /* Utility classes */
        .text-primary {
            color: var(--primary-color) !important;
        }
    
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
    
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }
    
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            color: white;
        }
    
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
    
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
    
        /* Form controls */
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid var(--border-color);
        }
    
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.15);
        }
    
        /* User profile container */
        .user-profile-container {
            width: 100%;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            border-top: 1px solid rgba(218, 220, 224, 0.5);
        }
    
        @media (min-width: 992px) {
            .user-profile-container {
                width: auto;
                padding-top: 0;
                margin-top: 0;
                border-top: none;
            }
        }
    
        .user-name, .user-role {
            display: block;
        }
    
        @media (max-width: 991px) {
            .btn-logout {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 0.5rem;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
            }
        }
    
        .navbar-toggler {
            border-color: rgba(218, 220, 224, 0.5);
        }
    
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(26, 115, 232, 0.15);
        }
    
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        
        .badge-primary {
            background-color: var(--primary-color);
        }

        .alert {
            background-color: var(--light);
            color: var(--bld);
            border-left: 4px solid var(--bld);
            border-radius: 0 8px 8px 0;
        }

        .alert-warn {
            background-color: var(--light-warn);
            color: var(--warning-color);
            border-left: 4px solid var(--warning-color);
            border-radius: 0 8px 8px 0;
        }   

        .alert-red {
            background-color: var(--light-danger);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
            border-radius: 0 8px 8px 0;
        }

        .page-link svg {
            width: 16px;
            height: 16px;
        }

    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="bi bi-building-gear me-2"></i> Facility Admin
            </a>
    
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
    
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-people me-1"></i> Users
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('users.list') }}">All Users</a></li>
                            <li><a class="dropdown-item" href="{{ route('users.deleted') }}">Deleted Users</a></li>
                        </ul>
                    </li>
    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-tags me-1"></i> Facility
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('facility-categories.index') }}">Facilities</a></li>
                            <li><a class="dropdown-item" href="{{ route('facilities.index') }}">Category</a></li>
                            <li><a class="dropdown-item" href="{{ route('facility-items.index') }}">Items</a></li>
                        </ul>
                    </li>
    
                    {{-- <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="itemDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-box-seam me-1"></i> Items
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">All Items</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Add New</a></li>
                        </ul>
                    </li> --}}
    
                    <li class="nav-item">
                        <a href="{{ route('bookings.index') }}" class="nav-link">
                            <i class="bi bi-calendar-check me-1"></i> Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}" class="nav-link">
                            <i class="bi bi-graph-up me-1"></i> Reports
                        </a>
                    </li>
                </ul>
    
                <div class="user-profile-container d-flex flex-column flex-lg-row align-items-center">
                    <div class="user-info d-flex align-items-center mb-2 mb-lg-0 me-lg-3">
                        <div class="user-avatar me-2">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <div class="user-name">{{ Auth::user()->name }}</div>
                            <div class="user-role">{{ Auth::user()->role }}</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-logout w-100">
                            <i class="bi bi-box-arrow-left me-1"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container-fluid py-3">
            @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize datetime picker

    
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
</body>
</html>