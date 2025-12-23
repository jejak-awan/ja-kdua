<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to Newsletter</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4f46e5;">Welcome, {{ $subscriber->name ?? 'Friend' }}!</h2>
        
        <p>Thank you for subscribing to our newsletter. We're excited to have you on board!</p>
        
        <p>You can expect to receive updates about:</p>
        <ul>
            <li>Latest articles and tutorials</li>
            <li>New features and product announcements</li>
            <li>Exclusive tips and resources</li>
        </ul>
        
        <p>If you ever want to unsubscribe, you can do so by clicking the link at the bottom of our emails.</p>
        
        <p style="margin-top: 30px;">
            Best regards,<br>
            The JA-CMS Team
        </p>

        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
        
        <p style="font-size: 12px; color: #666; text-align: center;">
            You received this email because you signed up for our newsletter.<br>
            <a href="{{ url('/') }}" style="color: #4f46e5;">Visit our website</a>
        </p>
    </div>
</body>
</html>
