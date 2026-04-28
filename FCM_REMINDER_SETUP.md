# FCM Medication Reminder Setup & Testing Guide

## Quick Start

The FCM notification system for medication reminders is now **fully integrated** into your system. Here's how to use it:

## Prerequisites

✅ Already configured:
- Firebase Cloud Messaging (FCM) setup in your project
- User model with `fcm_token` field
- NotificationLog table for tracking
- Service worker with notification support
- Dashboard reminder UI

## How It Works

### Automatic FCM Push Notifications

When a patient checks their dashboard (or via service worker):

1. **First Reminder** - When medication time arrives:
   - Push notification: "💊 Waktu Minum Obat"
   - Medicine name, dose, and time shown
   - Auto-opens dashboard when tapped

2. **Second Reminder** - 30 minutes later if not confirmed:
   - Push notification: "⏰ Pengingat Kedua - Minum Obat"
   - Reminder that medication wasn't confirmed yet

## Testing FCM Medication Reminders

### Method 1: Using the Built-in FCM Test Page

```
1. Go to: http://127.0.0.1:8000/fcm-test
2. Login as a user
3. Ensure your FCM token is saved
4. Create a medication scheduled for NOW (or next 5 minutes)
5. Call API: GET /api/due-medications
6. Check your device for push notification
```

### Method 2: Using curl/Postman

```bash
# Get due medications (will automatically send FCM)
curl -X GET http://127.0.0.1:8000/api/due-medications \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Response includes:
{
  "success": true,
  "due_medications": [
    {
      "medication_schedule_id": 1,
      "medicine_name": "Aspirin",
      "medicine_dose": "500 mg",
      "time": "09:00",
      "reminder_type": "dashboard"
    }
  ]
}
```

### Method 3: Browser Console

```javascript
// While logged in at dashboard
fetch('/api/due-medications', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  }
})
.then(r => r.json())
.then(data => console.log('Medications:', data));
```

## Viewing Logs

Monitor FCM notifications in real-time:

```bash
# Tail logs
tail -f storage/logs/laravel.log

# Search for FCM activities
grep -i "fcm\|medication reminder" storage/logs/laravel.log

# Check specific user's notifications
grep "user_id.*3" storage/logs/laravel.log | tail -50
```

## Database Queries

Check notification status:

```sql
-- All FCM reminders sent today
SELECT id, user_id, medication_schedule_id, sent_at, notification_type, status
FROM notification_logs
WHERE DATE(sent_at) = CURDATE()
AND notification_type = 'fcm'
ORDER BY sent_at DESC;

-- User's reminder history (past 7 days)
SELECT nl.*, m.name as medicine_name, u.name as user_name
FROM notification_logs nl
JOIN medication_schedules ms ON nl.medication_schedule_id = ms.id
JOIN medicines m ON ms.medicine_id = m.id
JOIN users u ON nl.user_id = u.id
WHERE nl.user_id = 3
AND nl.scheduled_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY nl.scheduled_time DESC;

-- Second reminders sent
SELECT id, user_id, medication_schedule_id, sent_at, second_reminder_sent_at
FROM notification_logs
WHERE second_reminder_sent_at IS NOT NULL
AND DATE(sent_at) = CURDATE();
```

## Common Issues & Solutions

### Issue: No FCM token for user
**Cause:** User hasn't registered their device
**Solution:** 
- Go to `/fcm-test` page
- Allow notifications in browser
- Token will be saved automatically

### Issue: Notifications not arriving
**Check:**
1. `tail storage/logs/laravel.log` - any FCM errors?
2. Is notification time past scheduled time?
3. User's notification preferences enabled? 
4. Not in Do-Not-Disturb window?
5. Check Firebase console for delivery status

**Solution:**
```php
// Check user preferences
SELECT notification_preferences FROM users WHERE id = 3;

// Should show something like:
// {"enabled":true,"dnd_start":"22:00","dnd_end":"08:00","sound_enabled":true}
```

### Issue: Duplicate notifications
**Already handled:** System prevents duplicates using NotificationLog
- Checks if already sent today before sending
- Uses `medication_schedule_id + user_id + date` as unique key

## Customization

### Change Second Reminder Delay

In `app/Http/Controllers/NotificationController.php`:

```php
// Change from 30 to 20 minutes
const SECOND_REMINDER_MINUTES = 20;
```

### Customize Notification Messages

In `app/Notifications/MedicationReminderNotification.php`:

```php
protected function getTitleAndBody(): array
{
    if ($this->reminderType === 'second') {
        return [
            'title' => '⏰ Jangan Lupa Minum Obat!', // Custom title
            'body' => "Apakah Anda sudah minum {$this->medicineName}?",
        ];
    }
    // ... first reminder customization
}
```

### Change Notification Sound/Color (Android)

In `MedicationReminderNotification.php`:

```php
'android' => [
    'notification' => [
        'color' => '#FF5722',        // Change color
        'sound' => 'your_sound_name', // Custom sound
        'tag' => 'medication-reminder-' . $this->medicationScheduleId,
    ],
],
```

## API Endpoints

Endpoints that automatically send FCM notifications:

| Endpoint | Method | Purpose | FCM Sent |
|---------|--------|---------|----------|
| `/api/due-medications` | GET | Get medications due now | Yes |
| `/api/second-reminders` | GET | Get pending second reminders | Yes |
| `/api/snooze-reminder-dashboard` | POST | Snooze reminder | No* |

*Snooze doesn't send FCM, just hides the alert locally

## Database Schema Reference

### notification_logs table fields relevant to FCM:

```sql
- id: Notification log identifier
- user_id: Which user
- medication_schedule_id: Which medication
- scheduled_time: When medicine should be taken
- sent_at: When FCM was sent
- status: 'sent', 'snoozed', 'dismissed', 'taken'
- notification_type: 'fcm', 'browser', 'sound', 'both'
- reminder_number: 1 (first) or 2 (second)
- second_reminder_at: When second reminder should trigger
- second_reminder_sent_at: When second reminder was actually sent
- snooze_until: When snoozed reminder should reappear
```

## Performance Notes

- FCM sends are **not queued** - sent synchronously during API call
- For high-volume systems, consider adding to queue:
  ```php
  // Add this to make it async:
  dispatch(new SendMedicationReminderNotification($user, $schedule));
  ```
- Each median reminder = 1 FCM API call to Firebase
- Typical FCM send time: 100-500ms
- Delivery time to device: 1-5 seconds (varies)

## Monitoring

Optional: Set up monitoring in your Firebase Console:

1. Go to Firebase Console → Messaging
2. View message delivery statistics
3. Check error rates
4. View device registration status

## Next: Advanced Setup (Optional)

For production systems, consider:

- [ ] Queue FCM sends for better performance
- [ ] Implement FCM token refresh handling
- [ ] Add notification read receipts
- [ ] Admin dashboard to view FCM delivery status
- [ ] Implement retry logic for failed sends
- [ ] Add notification templates/personalization
- [ ] Implement A/B testing for notification timing

---

**Status**: ✅ Fully Implemented & Ready to Test
**Date**: 2026-04-26
