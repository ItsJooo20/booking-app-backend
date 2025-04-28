<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Facility</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #1A73E8;      
            --secondary: #4285F4;    
            --light: #E8F0FE;      
            --dark: #0D47A1;         
            --accent: #34A853;       
            --hover: #2B7DE9;        
            --text: #202124;        
            --text-light: #5F6368;   
            --border: #DADCE0;      
        }
        
        body {
            background-color: #F8F9FA;        
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            line-height: 1.6;
        }
        
        .card {
            border-radius: 12px;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            letter-spacing: 0.25px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn:hover {
            background-color: var(--hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(26, 115, 232, 0.25);
        }
        
        .btn-outline {
            border: 1px solid var(--primary);
            color: var(--primary);
            background-color: transparent;
        }
        
        .btn-outline:hover {
            background-color: var(--light);
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 16px;
            border: 1px solid var(--border);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
        }
        
        .title {
            color: var(--dark);
            font-weight: 600;
            letter-spacing: -0.25px;
        }
        
        .logo-container {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(66, 133, 244, 0.15);
        }
        
        .forgot-password {
            color: var(--secondary);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s ease;
        }
        
        .forgot-password:hover {
            color: var(--primary);
            text-decoration: underline;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary);
        }
        
        .badge {
            background-color: var(--light);
            color: var(--primary);
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 20px;
        }
        
        /* Table styling */
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--light);
            color: var(--primary);
            font-weight: 500;
            border-bottom: 2px solid var(--border);
        }
        
        /* Alert styling */
        .alert {
            background-color: var(--light);
            color: var(--primary);
            border-left: 4px solid var(--primary);
            border-radius: 0 8px 8px 0;
        }
    </style>
</head>
<body>
<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="card border-0 p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center">
            {{-- <div class="logo-container mx-auto">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="#7C3AED"/>
                    <circle cx="12" cy="12" r="5" fill="#7C3AED"/>
                </svg>
            </div> --}}
            <h2 class="mb-3 title">Welcome Back</h2>
            <p class="text-muted mb-4">Please log in to continue</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger text-center">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label fw-medium mb-2">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="mail@example.com" required>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="password" class="form-label fw-medium mb-0">Password</label>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn py-2">Log In</button>
            </div>
            
            <div class="text-center mt-4">
                <span class="text-muted">Don't have an account?</span>
                <a href="#" class="forgot-password ms-1">Contact Admin</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>