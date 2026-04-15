# Email Notifications

## Overview
The dashboard sends automated email notifications to users for important upload events.

## Notification Types

### 1. Upload Completed Notification
**Class**: `App\Notifications\UploadCompletedNotification`
**Trigger**: After successful file upload and processing
**Content**:
- Confirmation message
- Number of records processed
- Upload date/time
- Link to dashboard
- Professional greeting

**Sample Email Subject**: "📤 Bestand succesvol geüpload – EcoCheck"

### 2. Upload Failed Notification
**Class**: `App\Notifications\UploadFailedNotification`
**Trigger**: If file upload or processing fails
**Content**:
- Error notification
- Error message details
- Supported file formats (xlsx, xls)
- Maximum file size (10 MB)
- Link to retry

**Sample Email Subject**: "⚠️ Upload mislukt – EcoCheck"

## Implementation Details

### Notification Configuration
- **Queue**: Uses ShouldQueue for async sending
- **Email Driver**: Configured in `.env` (MAIL_DRIVER)
- **Sender**: Uses application mail configuration

### Email Configuration (`.env`)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@ecocheck.local
MAIL_FROM_NAME="EcoCheck"
```

### Sending Mechanism
Notifications are sent in `UploadController::store()`:

```php
// Success notification
Auth::user()->notify(new UploadCompletedNotification($upload, $count));

// Failure notification
Auth::user()->notify(new UploadFailedNotification($upload, $e->getMessage()));
```

## Features

### ✅ Queue Support
- Notifications are queued and sent asynchronously
- Doesn't block upload response
- Can be run with `php artisan queue:listen`

### ✅ User Integration
- Notifications are sent to authenticated user
- Uses User model's email address
- User can manage preferences (future enhancement)

### ✅ Localization
- Dutch language content
- Supports future localization

### ✅ Customizable
- Email subjects with emojis
- Professional formatting with line breaks
- Includes action buttons linking to dashboard

## Queue Configuration

To process queued notifications:
```bash
# Job queue listener (production)
php artisan queue:work

# Single job processor
php artisan queue:listen --timeout=0
```

The application includes queue configuration for background job processing.

## Testing Notifications

### Mailtrap Testing
1. Sign up at mailtrap.io (free tier available)
2. Get SMTP credentials
3. Update `.env` with Mailtrap settings
4. Upload a file to trigger notification
5. Check Mailtrap inbox for received emails

### Local Testing
```bash
# Use log driver for local testing
MAIL_DRIVER=log

# Emails will appear in storage/logs/laravel.log
tail -f storage/logs/laravel.log
```

## Files Created
1. `app/Notifications/UploadCompletedNotification.php`
2. `app/Notifications/UploadFailedNotification.php`

## Future Enhancements

### Planned Features
1. **Email Preferences**: User settings to opt-in/out of notifications
2. **Daily Summaries**: Automated daily report of uploads
3. **Activity Digest**: Weekly summary of all activities
4. **Custom Templates**: Customizable email branding
5. **Multiple Channels**: SMS, push notifications, webhooks

### Notification Channels
```php
// Future: Multiple delivery channels
public function via(object $notifiable): array
{
    return ['mail', 'database', 'slack']; // SMS, webhooks, etc.
}
```

## Troubleshooting

### Notifications Not Sending
1. Check `.env` MAIL_DRIVER setting
2. Verify SMTP credentials
3. Check `jobs` table for queued notifications
4. Run `php artisan queue:listen` for queue processing
5. Check `storage/logs/laravel.log` for errors

### Missing Emails
- Ensure queue worker is running
- Check spam folder
- Verify sender domain not in blacklist

### Email Content Issues
- Check notification class formatting
- Verify User model has email attribute
- Test with log driver first
