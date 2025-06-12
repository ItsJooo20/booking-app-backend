<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .success-container { max-width: 400px; margin: 50px auto; }
        .card { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .success-icon { font-size: 3rem; color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="card">
                <div class="card-body text-center">
                    <div class="success-icon mb-3">✓</div>
                    <h4 class="mb-3">Password Reset Successful!</h4>
                    <p class="text-muted mb-4">Your password has been successfully updated. You can now log in with your new password.</p>
                    <a href="{{ url('/login') }}" class="btn btn-primary">Go to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>