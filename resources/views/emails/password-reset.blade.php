<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
        .content { padding: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Password Reset Request</h2>
        </div>
        
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            
            <p>You are receiving this email because we received a password reset request for your account.</p>
            
            <p>Click the button below to reset your password:</p>
            
            <a href="{{ $reset_url }}" class="button">Reset Password</a>
            
            <p>If you're having trouble clicking the button, copy and paste the URL below into your web browser:</p>
            <p><a href="{{ $reset_url }}">{{ $reset_url }}</a></p>
            
            <p>This password reset link will expire in 24 hours.</p>
            
            <p>If you did not request a password reset, no further action is required.</p>
        </div>
        
        <div class="footer">
            <p>Best regards,<br>{{ config('app.name') }} Team</p>
        </div>
    </div>
</body>
</html>