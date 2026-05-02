# OneSignal Notification System - Implementation Guide

## ✅ Current Implementation Status (May 2, 2026)

The LifeCare+ application now has a fully integrated OneSignal push notification system for medication reminders.

### What's Implemented

#### Backend Components
- ✅ **MedicationReminderNotification** - Laravel notification class sending to OneSignal
- ✅ **SendMedicationReminders** - Console command for scheduled reminders (first + second reminders)
- ✅ **NotificationController** - API endpoints for notifications and test notifications
- ✅ **OneSignalConfigService** - Configuration validation and diagnostics
- ✅ **CheckOneSignalConfig** - Artisan command to verify setup
- ✅ **NotificationLog Model** - Tracks sent notifications, snooze states, and reminder numbers

#### Frontend Components
- ✅ **OneSignal SDK v16** - Loaded from CDN in app_mobile.blade.php
- ✅ **OneSignalSDKWorker.js** - Service worker for web push notifications
- ✅ **SDK Initialization** - Proper error handling and subscription management
- ✅ **Push Subscription Listeners** - Tracks subscription status changes
- ✅ **Notification Event Handlers** - Handles click events from notifications

#### Notification Features
- ✅ **First Reminder** - Sent at scheduled medication time
- ✅ **Second Reminder** - Sent 30 minutes after scheduled time if not confirmed
- ✅ **Test Notifications** - Available in user profile for testing
- ✅ **Dashboard Reminders** - In-app reminder list with snooze functionality
- ✅ **Offline Support** - Messages queued when offline (via service worker)

---

## 🔧 Configuration

### Environment Variables (.env)

```env
# OneSignal Configuration
ONESIGNAL_APP_ID=d2adbb4d-c6c8-4ad3-86ac-3d8b4cc97434
ONESIGNAL_REST_API_KEY=os_v2_app_2kw3wtogzbfnhbvmhwfuzslugqnj6pqeafae2mug4nyzz3j6hbg2ituu2hvohc436mus5fsncqbkdhuyyxmau7ox3mvihvk2ddbf37i
ONESIGNAL_REST_API_URL=https://api.onesignal.com

# Queue Configuration (IMPORTANT for notifications)
QUEUE_CONNECTION=database
```

### Database Queue Setup

The application uses **database queue** for reliable notification delivery. Ensure:

1. **Queue table exists**: Run migration
   ```bash
   php artisan migrate
   ```

2. **Queue worker is running**: 
   ```bash
   # Development
   php artisan queue:work

   # Production (with restart on failure)
   php artisan queue:work --tries=3 --timeout=90
   ```

---

## ✅ Verification Checklist

### 1. Check Configuration
```bash
php artisan onesignal:check-config
```

Expected output:
```
✅ App ID Configured
✅ API Key Configured
✅ API URL Configured
✅ READY
```

### 2. Verify Database Queue
```bash
php artisan queue:failed
```

Should show no failed jobs if everything is working.

### 3. Test Notification
1. Login to app at `/app/dashboard`
2. Go to Profile → "Kirim Notifikasi Percobaan" button
3. Check browser/mobile for push notification
4. Look at Laravel logs: `storage/logs/laravel.log`

### 4. Check Logs
```bash
# Real-time logs
tail -f storage/logs/laravel.log

# Look for:
# [OneSignal] Initializing with AppID...
# [OneSignal] User logged in
# Test notification sent successfully
```

---

## 📋 How It Works

### Sending First Reminder

1. **User schedules medication** → MedicationSchedule created
2. **Scheduled time arrives** → SendMedicationReminders command runs (via cron/scheduler)
3. **Notification created** → MedicationReminderNotification instance sent via OneSignalChannel
4. **Queue processes** → Queue worker picks up the job
5. **OneSignal API call** → REST API sends to user's external ID (email)
6. **User receives push** → Browser/mobile displays notification
7. **Log recorded** → NotificationLog tracks delivery

### Sending Second Reminder (After 30 minutes)

1. **First reminder sent** → second_reminder_at calculated and stored
2. **30 minutes pass** → SendMedicationReminders runs again
3. **Checks if still pending** → If medication not taken, sends second reminder
4. **Second notification sent** → Same process as first reminder
5. **Log updated** → second_reminder_sent_at recorded

### Dashboard (In-App) Reminders

1. **Frontend polls** → `/api/due-medications` every 30 seconds
2. **Gets due medications** → Only within ±10 minute window
3. **Shows reminder list** → Above "Obat Hari Ini" section
4. **User confirms or snoozes** → Updates NotificationLog with snooze_until
5. **After snooze period** → Reminder re-appears

---

## 🚀 Testing Reminders

### Manual Test with Tinker

```bash
php artisan tinker

# Get a user
$user = App\Models\User::first();

# Send test notification
Notification::send($user, new App\Notifications\MedicationReminderNotification(
    'Obat Test',
    '2 Tablet',
    now()->format('H:i'),
    123,
    'first'
));

# Check logs
tail -f storage/logs/laravel.log
```

### Create Test Medication Schedule

```bash
php artisan tinker

$user = App\Models\User::first();

$schedule = App\Models\MedicationSchedule::create([
    'user_id' => $user->id,
    'medicine_id' => 1,
    'start_date' => now(),
    'time' => now()->format('H:i'),
    'frequency' => 'daily',
    'is_active' => true,
]);

# Wait and watch for notification
tail -f storage/logs/laravel.log
```

### Run Reminder Command

```bash
# Send pending reminders immediately
php artisan medication:send-reminders

# This will:
# 1. Check all active users
# 2. Find medications due within time window
# 3. Send first reminders
# 4. Check for second reminders needing sending
# 5. Log all activities
```

---

## 📊 Monitoring & Troubleshooting

### Check Queue Status

```bash
# See pending jobs
php artisan queue:work --verbose

# Monitor failed jobs
php artisan queue:failed
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### View Sent Notifications

```sql
-- Check notification logs
SELECT * FROM notification_logs 
ORDER BY created_at DESC 
LIMIT 20;

-- Check pending reminders
SELECT * FROM notification_logs 
WHERE status = 'sent' 
AND second_reminder_at <= NOW() 
AND second_reminder_sent_at IS NULL;
```

### Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| No notifications arriving | Check queue worker running: `php artisan queue:work` |
| "Invalid App ID" error | Verify ONESIGNAL_APP_ID is set in .env |
| "API Key required" error | Verify ONESIGNAL_REST_API_KEY is set in .env |
| Notifications in test but not automatic | Check `php artisan medication:send-reminders` runs |
| Queue worker keeps crashing | Check for PHP errors: `php artisan queue:work --verbose` |
| Database queue not working | Ensure migrations ran: `php artisan migrate` |

### Enable Debug Logging

Add to `.env`:
```env
LOG_LEVEL=debug
```

### Check OneSignal Dashboard

1. Go to https://onesignal.com/dashboard
2. Select LifeCare+ app
3. Check "Messages" section for delivery status
4. View delivery analytics

---

## 🔐 Security Considerations

### Email as External User ID

- User emails are used as OneSignal **external user ID**
- Enables server-side targeting via email
- Ensure users have valid, unique emails

### Queue Database

- Notifications stored in `jobs` table temporarily
- Failed jobs stored in `failed_jobs` table
- Consider archiving old logs for disk space

### API Key Security

- Store ONESIGNAL_REST_API_KEY in .env (never in code)
- Restrict API key permissions in OneSignal dashboard
- Rotate keys if compromised

---

## 🔄 Scheduled Tasks

To automatically send reminders, configure Laravel Scheduler:

### Option 1: Cron Job (Recommended for Production)

```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

### Option 2: Manual Schedule

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->command('medication:send-reminders')
        ->everyFiveMinutes()
        ->withoutOverlapping()
        ->onFailure(function () {
            Log::error('Medication reminder command failed');
        });
}
```

---

## 📝 Files Modified/Created

### New Files
- `app/Services/OneSignalConfigService.php` - Configuration validation
- `app/Console/Commands/CheckOneSignalConfig.php` - Verification command

### Modified Files
- `resources/views/layouts/app_mobile.blade.php` - Enhanced SDK initialization
- `app/Notifications/MedicationReminderNotification.php` - Added icons, badges, better error handling
- `app/Http/Controllers/NotificationController.php` - Configuration validation and enhanced logging

### Existing Files (Already Working)
- `app/Models/User.php` - routeNotificationForOneSignal()
- `app/Models/NotificationLog.php` - Tracks notifications
- `app/Console/Commands/SendMedicationReminders.php` - Sends reminders
- `routes/web.php` - Notification endpoints
- `public/OneSignalSDKWorker.js` - Service worker

---

## 💡 Next Steps

1. **Verify Setup**: Run `php artisan onesignal:check-config`
2. **Start Queue Worker**: `php artisan queue:work`
3. **Test**: Send test notification from profile page
4. **Monitor**: Watch logs and queue status
5. **Deploy**: Add scheduler to production cron

---

## Support Resources

- [OneSignal Documentation](https://documentation.onesignal.com/)
- [Laravel Notifications](https://laravel.com/docs/notifications)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [OneSignal REST API](https://documentation.onesignal.com/reference/rest-api)

---

**Last Updated**: May 2, 2026
**System Status**: ✅ Production Ready
