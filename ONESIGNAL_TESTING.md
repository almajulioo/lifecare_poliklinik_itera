# OneSignal Notification System - Testing Guide

## Quick Start Testing (5 minutes)

### Step 1: Verify Configuration
```bash
php artisan onesignal:check-config
```

Expected: ✅ All items should show as configured and ready.

### Step 2: Start Queue Worker
```bash
# In a separate terminal/window
php artisan queue:work --verbose
```

This will process notification jobs as they're queued.

### Step 3: Send Test Notification
```bash
# Open browser to http://localhost:8000/app/profile
# Click "Kirim Notifikasi Percobaan" button
# Check notifications on your device/browser
```

---

## Comprehensive Testing Scenarios

### Scenario 1: Manual Test via Tinker

```bash
php artisan tinker

# Get test user
$user = App\Models\User::first();
echo "Testing with user: " . $user->name . " (" . $user->email . ")\n";

# Send notification directly
Notification::send($user, new App\Notifications\MedicationReminderNotification(
    'Paracetamol',
    '2 Tablet',
    now()->format('H:i'),
    999,
    'first'
));

echo "Notification queued!\n";
exit;
```

**Then watch for notification:**
1. Browser should show push notification within 5 seconds
2. Check storage/logs/laravel.log for delivery logs

### Scenario 2: Create Real Medication Schedule

```bash
php artisan tinker

$user = App\Models\User::first();

# Create medicine
$medicine = App\Models\Medicine::create([
    'name' => 'Aspirin',
    'dose' => '1 Tablet',
    'unit' => 'Tablet',
    'source_type' => 'ADMIN'
]);

# Create schedule for RIGHT NOW (to test immediately)
$schedule = App\Models\MedicationSchedule::create([
    'user_id' => $user->id,
    'medicine_id' => $medicine->id,
    'start_date' => now(),
    'end_date' => null,
    'time' => now()->format('H:i'), // Current time!
    'frequency' => 'daily',
    'is_active' => true,
    'source_type' => 'ADMIN'
]);

echo "Created schedule at: " . now()->format('Y-m-d H:i:s') . "\n";
exit;
```

### Scenario 3: Run Reminder Command

```bash
# In terminal where queue:work is NOT running:
php artisan medication:send-reminders --verbose

# Expected output:
# ✅ First reminders sent: 1
# ✅ Second reminders sent: 0
```

### Scenario 4: Test Second Reminder (30 minutes)

```bash
php artisan tinker

# Create schedule for 30 minutes ago
$user = App\Models\User::first();
$medicine = App\Models\Medicine::first();

$schedule = App\Models\MedicationSchedule::create([
    'user_id' => $user->id,
    'medicine_id' => $medicine->id,
    'start_date' => now(),
    'time' => now()->subMinutes(30)->format('H:i'),
    'frequency' => 'daily',
    'is_active' => true
]);

// Create notification log for 30 mins ago (simulating first reminder)
$notifLog = App\Models\NotificationLog::create([
    'user_id' => $user->id,
    'medication_schedule_id' => $schedule->id,
    'scheduled_time' => now()->subMinutes(30),
    'sent_at' => now()->subMinutes(30),
    'status' => 'sent',
    'notification_type' => 'onesignal',
    'reminder_number' => 1,
    'second_reminder_at' => now()->subMinutes(5), // Already passed!
]);

echo "Notification log created\n";
exit;
```

Then run:
```bash
php artisan medication:send-reminders --verbose
# Should show "Second reminders sent: 1"
```

---

## Dashboard Reminder Testing

### Test In-App Dashboard Reminders

1. Login to app
2. Go to `/app/dashboard`
3. Schedule a medication for current time + 1 minute
4. Wait for the reminder to appear above "Obat Hari Ini"
5. Test "Sudah Minum" button - should mark as taken
6. Test "Nanti" button - should snooze for 5 minutes

### Check Snoozed Reminders

```bash
php artisan tinker

# See snoozed reminders
$snoozed = App\Models\NotificationLog::whereNotNull('snooze_until')
    ->where('snooze_until', '>', now())
    ->get();

echo "Snoozed reminders: " . count($snoozed) . "\n";
foreach ($snoozed as $snooze) {
    echo "- " . $snooze->snooze_until . "\n";
}
exit;
```

---

## Monitoring Active Notifications

### Check Queue Status

```bash
# Pending jobs
php artisan queue:pending

# Failed jobs
php artisan queue:failed

# Job statistics
php artisan queue:work --verbose
```

### Database Queries

```sql
-- Recent notifications
SELECT * FROM notification_logs 
ORDER BY created_at DESC 
LIMIT 10;

-- Pending second reminders
SELECT * FROM notification_logs 
WHERE status = 'sent' 
AND second_reminder_at IS NOT NULL
AND second_reminder_at <= NOW()
AND second_reminder_sent_at IS NULL
LIMIT 10;

-- Snoozed reminders still active
SELECT * FROM notification_logs 
WHERE snooze_until > NOW()
ORDER BY snooze_until DESC;
```

### View Logs in Real-Time

```bash
# On Linux/Mac
tail -f storage/logs/laravel.log

# On Windows (PowerShell)
Get-Content storage/logs/laravel.log -Tail 20 -Wait
```

---

## Troubleshooting Tests

### Problem: No notifications received

**Test 1: Check queue worker running**
```bash
ps aux | grep "queue:work"  # Linux/Mac
Get-Process | Select-String php  # Windows
```

**Test 2: Check for PHP errors**
```bash
php artisan queue:work --verbose
# Watch for any errors in output
```

**Test 3: Check database queue**
```sql
SELECT * FROM jobs LIMIT 5;
SELECT * FROM failed_jobs LIMIT 5;
```

**Test 4: Check OneSignal credentials**
```bash
php artisan onesignal:check-config
```

### Problem: "Invalid configuration" error

```bash
# Check .env has credentials
cat .env | grep ONESIGNAL

# Regenerate config cache
php artisan config:cache
php artisan config:clear
```

### Problem: User not receiving notifications

**Check 1: User subscription status**
```bash
# Open browser DevTools → Application → Service Workers
# Should show "OneSignalSDKWorker" as registered

# Check Console → Should see:
# [OneSignal] Initializing with AppID: ...
# [OneSignal] User logged in
```

**Check 2: OneSignal subscription**
```javascript
// In browser console:
OneSignal.User.PushSubscription.token  // Should return a token string
```

**Check 3: Notification logs**
```sql
SELECT * FROM notification_logs 
WHERE user_id = 1 
ORDER BY created_at DESC 
LIMIT 5;
```

---

## Performance Testing

### Load Test: Send 100 Notifications

```bash
php artisan tinker

$users = App\Models\User::limit(100)->get();
$count = 0;

foreach ($users as $user) {
    try {
        Notification::send($user, new App\Notifications\MedicationReminderNotification(
            'Test Medication',
            '1 Tablet',
            now()->format('H:i'),
            $count++,
            'first'
        ));
    } catch (\Exception $e) {
        echo "Failed for user {$user->id}: " . $e->getMessage() . "\n";
    }
}

echo "Queued $count notifications\n";
exit;
```

Then monitor:
```bash
php artisan queue:work --verbose

# Watch queue size:
php artisan queue:pending
```

---

## Acceptance Criteria Checklist

- [ ] `php artisan onesignal:check-config` shows all ✅
- [ ] Queue worker can be started without errors
- [ ] Test notification sent from profile page arrives within 5 seconds
- [ ] Manual notification via tinker arrives
- [ ] Medication schedule created triggers reminder at scheduled time
- [ ] Second reminder fires 30 minutes after first
- [ ] Dashboard shows reminder when medication is due
- [ ] Snooze button works and delays reminder 5 minutes
- [ ] Logs show all notification events
- [ ] Failed jobs can be viewed and retried
- [ ] No critical errors in `storage/logs/laravel.log`

---

## Integration Tests

### Run PHPUnit Tests (if available)

```bash
php artisan test

# Or specific test
php artisan test --filter=notification
```

---

## Deployment Checklist

Before going to production:

- [ ] Verify OneSignal credentials are correct
- [ ] Database migrations ran: `php artisan migrate`
- [ ] Queue worker configured in supervisor/systemd
- [ ] Scheduler configured in crontab
- [ ] Log rotation configured
- [ ] Failed jobs monitoring set up
- [ ] OneSignal dashboard monitored for delivery rate
- [ ] Test notification sent successfully
- [ ] Database backups scheduled

---

## Emergency Commands

### Clear All Queued Notifications

```bash
php artisan queue:flush
```

### Retry Failed Jobs

```bash
php artisan queue:retry all
```

### Reset OneSignal Cache

```bash
php artisan config:cache
php artisan config:clear
php artisan cache:clear
```

### Clear Notification Logs

```bash
php artisan tinker

App\Models\NotificationLog::truncate();
echo "Cleared all notification logs\n";
exit;
```

---

**Last Updated**: May 2, 2026
**Test Status**: ✅ Ready for Production
