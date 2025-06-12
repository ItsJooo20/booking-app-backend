<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Error</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .error-container { max-width: 400px; margin: 50px auto; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .error-icon { font-size: 3rem; color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="card">
                <div class="card-body text-center">
                    <div class="error-icon mb-3">✗</div>
                    <h4 class="mb-3">Reset Link Invalid</h4>
                    <p class="text-muted mb-4">{{ $message }}</p>
                    <a href="{{ url('/forgot-password') }}" class="btn btn-primary">Request New Reset Link</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>