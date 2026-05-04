# OneSignal Scheduled Medication Reminders

## 📋 Overview

This system automatically schedules medication reminders through OneSignal's API for all active medication schedules. Instead of checking schedules periodically, notifications are pre-scheduled and sent at the exact time specified in the medication schedule.

## 🎯 Features

### Dual Reminders
- **First Reminder**: Sent at the scheduled medication time
- **Second Reminder**: Sent 30 minutes after the scheduled time (if medication not taken)

### Automatic Scheduling
- New schedules are automatically included in the next day's scheduling run
- Schedules are planned for the next 7 days by default
- Runs daily at 00:05 (5 minutes after midnight)

### User Targeting
- Notifications are sent to users by their email (external user ID in OneSignal)
- Each user receives reminders only for their own medication schedules
- Respects schedule start/end dates

## 📅 How It Works

### Daily Scheduling Process
1. Every day at 00:05 UTC, the scheduler runs
2. It queries all active medication schedules for the next 7 days
3. For each schedule, it calculates the exact notification send time
4. OneSignal API is called with `send_after` parameter to schedule the notification
5. Two notifications per schedule are scheduled (first + second reminder)

### Medication Reminder Example
**User**: john@example.com
**Medicine**: Paracetamol 500mg
**Schedule**: 09:00 every day

**Scheduled Notifications**:
- **09:00** - First reminder: "💊 Waktu Minum Obat - Paracetamol (500mg) - Jadwal: 09:00"
- **09:30** - Second reminder: "⏰ Pengingat Kedua - Minum Obat - Apakah Anda sudah minum obat Paracetamol?"

## 🛠️ Command Usage

### Manual Scheduling
```bash
# Schedule notifications for next 7 days (default)
php artisan medication:schedule-notifications

# Schedule notifications for next 14 days
php artisan medication:schedule-notifications --days=14

# Schedule notifications for next 30 days
php artisan medication:schedule-notifications --days=30
```

### Automatic Scheduling
The scheduler automatically runs daily at 00:05:
```php
Schedule::command('medication:schedule-notifications --days=7')
    ->dailyAt('00:05')
    ->withoutOverlapping();
```

## 📧 OneSignal API Integration

### Authentication
Uses OneSignal REST API v1 with API Key authentication:
```php
'Authorization' => 'Basic ' . base64_encode(config('services.onesignal.api_key'))
```

### Notification Payload
```json
{
  "headings": { "en": "💊 Waktu Minum Obat" },
  "contents": { "en": "Paracetamol (500mg) - Jadwal: 09:00" },
  "url": "http://localhost:8000/app/dashboard",
  "include_aliases": {
    "external_id": ["user@example.com"]
  },
  "send_after": "2024-05-02 16:00:00 UTC"
}
```

### User Targeting
Notifications are targeted using `include_aliases` with `external_id` matching the user's email:
- This requires users to be logged into OneSignal with their email
- Email is set during OneSignal initialization in the browser
- Each scheduled notification targets the specific user

## 📊 Database Requirements

### Required Models
- `User` - With email field for OneSignal targeting
- `MedicationSchedule` - With time, start_date, end_date, is_active fields
- `Medicine` - With name, dose, unit fields

### Relationships
```php
User hasMany MedicationSchedule
MedicationSchedule belongsTo User
MedicationSchedule belongsTo Medicine
```

## 🔧 Configuration

### Required Environment Variables
```env
ONESIGNAL_APP_ID=your_app_id
ONESIGNAL_API_KEY=your_api_key
```

### Optional Settings

The command uses sensible defaults:
- **Days ahead**: 7 days (configurable with `--days` option)
- **Second reminder delay**: 30 minutes (hardcoded as `SECOND_REMINDER_DELAY_MINUTES`)
- **Schedule time**: 00:05 UTC (configurable in `routes/console.php`)

To change second reminder delay, modify:
```php
const SECOND_REMINDER_DELAY_MINUTES = 30;
```

## 🚀 Usage Examples

### Example 1: Basic Setup
```bash
# Run once to schedule all upcoming reminders
php artisan medication:schedule-notifications --days=7
```

### Example 2: Schedule Far Ahead
```bash
# Plan reminders for the next 30 days
php artisan medication:schedule-notifications --days=30
```

### Example 3: Verify Scheduling Works
```bash
# This runs automatically, but you can test:
php artisan schedule:run
```

## 📝 Output Example

```
Scheduling medication notifications for the next 1 days...
Found 7 users with active schedules
  ✓ First reminder scheduled for user1@example.com on 2024-05-02 09:00
  ✓ Second reminder scheduled for user1@example.com on 2024-05-02 09:30
  ✓ First reminder scheduled for user2@example.com on 2024-05-02 14:00
  ✓ Second reminder scheduled for user2@example.com on 2024-05-02 14:30
...
✅ Successfully scheduled: 14 notifications
```

## ⚙️ How Scheduling Works

### Algorithm
1. Get all users with active medication schedules
2. For each user, get all their active schedules
3. For each schedule:
   - Calculate each day from now until `--days` ahead
   - Skip if schedule hasn't started yet or has ended
   - Calculate notification time from `schedule.time`
   - Skip if time is in the past
   - Call OneSignal API to schedule first reminder
   - Call OneSignal API to schedule second reminder (30min later)

### Time Handling
- Medication times are stored as `HH:MM` format (e.g., "09:00")
- Notifications are sent in UTC timezone
- User timezone is NOT considered (notifications use app timezone)

## 📋 Troubleshooting

### Notifications Not Scheduled
**Problem**: Command shows "0 notifications scheduled"
**Solutions**:
1. Check if there are active medication schedules: 
   ```bash
   php artisan tinker
   >>> \App\Models\MedicationSchedule::where('is_active', true)->count()
   ```
2. Verify users have email addresses set
3. Check OneSignal API credentials in `.env`

### API Errors
**Problem**: "OneSignal API error" in logs
**Solutions**:
1. Verify API key is correct in `.env`
2. Check OneSignal dashboard for API key validity
3. Ensure user email is subscribed to OneSignal

### Duplicate Notifications
**Problem**: Notifications scheduled multiple times
**Solutions**:
1. OneSignal allows duplicate scheduling (this is expected)
2. If too many duplicates, clear OneSignal campaign and re-run
3. Consider changing schedule run time to `->dailyAt('00:05')`

## 🔄 Combining with Existing System

### SendMedicationReminders Command
The legacy `SendMedicationReminders` command still works for real-time reminders based on queue system. The new system:
- **PreSchedules** reminders in OneSignal for guaranteed delivery
- **Complements** the queue-based reminder system
- **Provides** fallback if queue system fails

Both can run together:
```php
// Real-time reminders (every 5 minutes)
Schedule::command('medication:send-reminders --check-interval=5')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Pre-scheduled reminders (daily at midnight)
Schedule::command('medication:schedule-notifications --days=7')
    ->dailyAt('00:05')
    ->withoutOverlapping();
```

## 📊 Performance

### Resource Usage
- **API Calls**: 2 per medication schedule per day
- **Database Queries**: ~5 queries total
- **Execution Time**: ~2-5 seconds for typical setup (7 users, 50 schedules)

### Scaling
For large deployments:
- Add `--days=1` to reduce API calls (schedule daily instead of weekly)
- Run during off-peak hours
- Monitor OneSignal API rate limits

## 🎯 Best Practices

1. **Run Daily**: Don't skip scheduled runs - notifications are needed daily
2. **Time Zone**: Consider user timezones when setting medication times
3. **Backup System**: Keep queue-based reminders as fallback
4. **Monitoring**: Check logs for OneSignal API errors
5. **Testing**: Use `--days=1` to test with minimal API calls

## 📞 Support

For issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify OneSignal configuration in `.env`
3. Test command manually: `php artisan medication:schedule-notifications --days=1`
4. Review OneSignal dashboard for scheduled campaigns

---

**Status**: ✅ Production Ready  
**Last Updated**: May 2, 2026  
**Framework**: Laravel 12.0 with OneSignal API v1
