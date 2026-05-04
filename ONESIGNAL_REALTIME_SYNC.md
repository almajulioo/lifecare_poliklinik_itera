# OneSignal Real-Time Schedule Synchronization

**Status**: ✅ Implemented & Tested  
**Date**: May 2, 2026

## Overview

Sistem telah dikonfigurasi untuk melakukan sinkronisasi real-time antara jadwal obat yang diinput pasien dengan OneSignal Scheduled Messages. Notifikasi akan otomatis dijadwalkan dan muncul di device pasien pada waktu yang tepat.

## Fitur Utama

### 1. Real-Time Sync saat Create/Update Schedule
- **Pemicu**: Pasien membuat atau mengubah jadwal obat
- **Proses**: Observer otomatis memanggil OneSignalSyncService
- **Hasil**: Notifikasi dijadwalkan di OneSignal untuk 30 hari ke depan
- **User Experience**: Tidak perlu konfigurasi manual, semua otomatis

### 2. Batch Scheduling (Optional)
```bash
php artisan medication:schedule-notifications --days=7
```
- Menyelaraskan semua jadwal aktif dengan OneSignal
- Berguna untuk recovery atau bulk update
- Menghitung notifikasi yang sudah scheduled vs baru

### 3. Timezone-Aware Scheduling
- Obat yang dijadwalkan di jam 20:55 Jakarta (UTC+7) akan dikirim di 13:55 UTC
- OneSignal API menerima waktu dalam format ISO 8601: `2026-05-02T13:55:00Z`
- Sistem otomatis mengonversi berdasarkan timezone user

## Arsitektur Implementasi

### Files & Components

**1. OneSignalSyncService** (`app/Services/OneSignalSyncService.php`)
```
✓ Baru dibuat
✓ Menangani sync logic untuk single schedule
✓ Method: syncScheduleToOneSignal()
✓ Method: deactivateScheduleFromOneSignal()
✓ Scheduling loop: 30 hari ke depan
✓ First reminder + second reminder (30 min delay)
```

**2. MedicationScheduleObserver** (Updated)
```
✓ Hook ke created event → syncOneSignal()
✓ Hook ke updated event → syncOneSignal()
✓ Hook ke deleted event → syncOneSignalDelete()
✓ Tetap maintain clinic patient status sync
✓ Sekarang: dual sync (clinic patient + OneSignal)
```

**3. ScheduleMedicationNotifications Command** (Refactored)
```
✓ Menggunakan OneSignalSyncService
✓ Lebih simple, delegasi ke service
✓ Tetap support --days option
✓ Loop schedule dan panggil service
```

## Workflow Lengkap

### Scenario 1: Pasien Input Jadwal Baru

```
[Pasien buat jadwal] 
    ↓
[Eloquent: MedicationSchedule created]
    ↓
[Observer triggered: created()]
    ↓
[OneSignalSyncService.syncScheduleToOneSignal()]
    ↓
[Loop 30 hari, buat notifikasi untuk tiap hari]
    ↓
[OneSignal API: POST /notifications dengan send_after]
    ↓
[OneSignal Scheduled Messages: Catat & schedule]
    ↓
[Waktu tiba] → [Notification ke device pasien] ✓
```

### Scenario 2: Pasien Update Jadwal

```
[Pasien ubah jadwal]
    ↓
[Eloquent: MedicationSchedule updated]
    ↓
[Observer triggered: updated()]
    ↓
[OneSignalSyncService.syncScheduleToOneSignal()]
    ↓
[Reschedule: Delete existing + create new notifications] ⚠️
    ↓
[OneSignal API: POST /notifications dengan updated times]
```

**Note**: OneSignal API tidak support cancel scheduled notifications yang sudah dikirim, jadi:
- Notifikasi lama tetap deliver (sudah di queue)
- Notifikasi baru akan dijadwalkan dengan waktu yang baru
- User mungkin dapat notifikasi ganda (design limitation)

### Scenario 3: Pasien Hapus/Deactivate Jadwal

```
[Pasien hapus jadwal]
    ↓
[Eloquent: MedicationSchedule deleted]
    ↓
[Observer triggered: deleted()]
    ↓
[OneSignalSyncService.deactivateScheduleFromOneSignal()]
    ↓
[Mark schedule sebagai inactive di local DB]
    ↓
[OneSignal: Notifikasi yang sudah scheduled tetap deliver]
    ↓
[Notifikasi baru tidak akan dijadwalkan]
```

## API Format

### OneSignal Scheduled Message Payload

```json
{
  "include_aliases": {
    "external_id": ["pasien@email.com"]
  },
  "headings": {"en": "💊 Waktu Minum Obat"},
  "contents": {"en": "Metformin (500 mg) - Jadwal: 08:00"},
  "url": "https://app.example.com/app/dashboard",
  "send_after": "2026-05-03T01:00:00Z",
  "data": {
    "schedule_id": 123,
    "medicine_id": 45,
    "reminder_type": "first"
  }
}
```

### Important Fields

| Field | Value | Note |
|-------|-------|------|
| `include_aliases` | `external_id` | Targeting by user email |
| `send_after` | ISO 8601 UTC | Format: `YYYY-MM-DDTHH:mm:ssZ` |
| `data` | Custom fields | Untuk tracking & analytics |

## Timezone Handling

```php
// Input: User di Jakarta (UTC+7), Jadwal 20:55
$notificationTimeLocal = Carbon::createFromFormat(
    'Y-m-d H:i',
    '2026-05-02 20:55',
    'Asia/Jakarta'  // ← User timezone
);

// Output: Konversi ke UTC
$notificationTimeUtc = $notificationTimeLocal->copy()->setTimezone('UTC');
// Result: 2026-05-02 13:55 UTC

// Format untuk OneSignal
$sendAfter = $notificationTimeUtc->format('Y-m-d\TH:i:s\Z');
// Result: "2026-05-02T13:55:00Z"
```

## Testing

### Test 1: Create Schedule & Verify Sync

```bash
# SSH/Login ke app
php tinker

# Buat schedule baru
$user = User::find(1);
$schedule = MedicationSchedule::create([
    'user_id' => $user->id,
    'medicine_id' => 1,
    'start_date' => now()->date(),
    'end_date' => now()->addDays(30)->date(),
    'time' => '09:00',
    'frequency' => 'once_daily',
    'is_active' => true
]);

# Check logs untuk verify OneSignal sync
exit
tail -f storage/logs/laravel.log
# Cari: "OneSignal sync completed" atau "Notification scheduled successfully"
```

### Test 2: Batch Sync Command

```bash
php artisan medication:schedule-notifications --days=7

# Output:
# Scheduling medication notifications for the next 7 days...
# Current UTC time: 2026-05-02 14:06:27
# Scheduling for: 2026-05-02 to 2026-05-09
# Found 8 users with active schedules
# ✓ Synced: user1@example.com - Medicine1
# ✓ Synced: user2@example.com - Medicine2
# ✅ Successfully scheduled: 16 notifications
```

### Test 3: Monitor OneSignal Dashboard

1. Login ke OneSignal Dashboard: https://dashboard.onesignal.com
2. Pilih app "lifecare_poliklinik_itera"
3. Navigasi ke: Campaigns → Messages
4. Filter: Status = "Scheduled"
5. Verifikasi: 
   - Timestamp sesuai dengan jam user local
   - Recipient = email pasien
   - Message content sesuai nama obat

## Monitoring & Troubleshooting

### Check Scheduled Notifications

```bash
# Lihat log untuk sync activity
tail -50 storage/logs/laravel.log | grep -i "onesignal\|notification"

# Check untuk errors
grep -i "error\|failed" storage/logs/laravel.log
```

### Verify OneSignal Sync

```bash
# Check schedule yang sudah sync
SELECT * FROM medication_schedules WHERE is_active = 1 LIMIT 5;

# Lihat user timezone
SELECT id, email, timezone FROM users WHERE id IN (1,2,3);
```

### If Notifications Not Appearing

1. **Verify Configuration**
   ```bash
   php artisan onesignal:check-config
   ```

2. **Check OneSignal Dashboard**
   - Campaigns → Messages → Filter by Scheduled
   - Verify timestamps and recipient count

3. **Check Laravel Logs**
   ```bash
   grep "OneSignal API error" storage/logs/laravel.log
   ```

4. **Test Manual Sync**
   ```bash
   php artisan medication:schedule-notifications --days=1
   ```

## Important Notes

### ⚠️ OneSignal API Limitations

1. **Cannot Cancel Scheduled Notifications**
   - Solusi: Tidak re-schedule jika schedule sudah inactive
   - Implication: Update jadwal bisa menghasilkan notifikasi ganda

2. **Scheduled Message Limit**
   - OneSignal mungkin ada batch limit untuk scheduled messages
   - Monitoring: Cek response dari API jika jumlah schedule besar

3. **Timezone Dependency**
   - Must populate `users.timezone` column
   - Default ke `config('app.timezone')` if null
   - Incorrect timezone = wrong notification time

### ✅ Best Practices

1. **Always Set User Timezone**
   - Saat user register/update profile
   - Validate dengan timezone list: `DateTimeZone::listIdentifiers()`

2. **Monitor Sync Performance**
   - Batch command bisa slow kalau banyak schedules
   - Consider: Run di off-peak hours
   - Setup: Schedule command di cron: `0 2 * * * cd /app && php artisan medication:schedule-notifications --days=30`

3. **Handle Sync Failures**
   - Check logs regularly
   - Setup alert untuk "OneSignal API error"
   - Retry mechanism: manual re-run command

## Files Modified/Created

| File | Status | Purpose |
|------|--------|---------|
| `app/Services/OneSignalSyncService.php` | ✅ Created | Main sync logic |
| `app/Observers/MedicationScheduleObserver.php` | ✅ Updated | Add OneSignal sync hooks |
| `app/Console/Commands/ScheduleMedicationNotifications.php` | ✅ Refactored | Use new service |

## Verification Checklist

- [x] OneSignalSyncService created dengan proper error handling
- [x] MedicationScheduleObserver updated untuk call service
- [x] ScheduleMedicationNotifications refactored untuk use service
- [x] Timezone conversion implemented correctly
- [x] ISO 8601 UTC timestamp format correct
- [x] Batch command tested & working: 16 notifications scheduled
- [x] Observer tested & real-time sync working
- [x] Logging implemented untuk debugging

## Next Steps (Optional Enhancements)

1. **Cancel Mechanism** (Workaround)
   - Maintain `one_signal_notification_id` di DB
   - Ketika update: track old notification IDs
   - Display warning: "Previous notifications may still be delivered"

2. **Notification History**
   - Create table `notification_deliveries` untuk track delivered notifications
   - Webhook dari OneSignal untuk delivery status

3. **User Settings**
   - Allow user untuk disable notifications
   - Custom reminder times (bukan hanya 30 min delay)
   - Quiet hours: jangan kirim notif di jam-jam tertentu

4. **Analytics**
   - Track delivery rate
   - Track open rate
   - User engagement metrics

---

**Status**: ✅ **PRODUCTION READY**  
**Last Updated**: May 2, 2026  
**Tested**: Batch schedule + real-time observer sync both working
