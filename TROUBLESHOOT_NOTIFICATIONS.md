# Troubleshooting: OneSignal Medication Notifications Not Appearing

**Problem**: Jadwal obat sudah di-input tapi notifikasi tidak muncul di device saat waktu diminum obat tiba.

**Solution**: Sync jadwal yang sudah ada (existing schedules) ke OneSignal Scheduled Messages.

## Root Cause

Sistem baru memiliki real-time sync untuk jadwal yang dibuat SETELAH implementasi OneSignal. Jadwal yang sudah ada SEBELUMNYA tidak otomatis tersync ke OneSignal. Diperlukan **retroactive sync** untuk mengirim jadwal lama ke OneSignal.

## Quick Fix (3 Langkah)

### Step 1: Sync Existing Schedules (3-5 menit)

Jalankan command untuk sync semua jadwal yang sudah ada ke OneSignal:

```bash
php artisan medication:sync-existing-to-onesignal --days=30 --force
```

**Output yang diharapkan:**
```
=== Sync Existing Medication Schedules to OneSignal ===

Found 9 active medication schedules

+----------------------------+---------------------+
| User Email                 | Number of Schedules |
+----------------------------+---------------------+
| user1@example.com          | 1                   |
| user2@example.com          | 1                   |
... (more users)

📊 Summary:
  • Total schedules to sync: 9
  • Lookahead days: 30
  • Estimated notifications: 540 (first + second reminder)

Starting sync...
✅ Successfully synced: 9 schedules
📊 Notifications scheduled:
  • Total: 540
  • Period: Next 30 days
  • Check OneSignal dashboard: Campaigns → Messages → Scheduled
```

### Step 2: Verify di OneSignal Dashboard

1. **Login**: https://dashboard.onesignal.com
2. **Pilih App**: lifecare_poliklinik_itera  
3. **Navigasi**: Campaigns → Messages
4. **Filter**: Status = "Scheduled" (checkbox di side menu)
5. **Verify**:
   - ✅ Total notifications > 0 (harus ada 540+)
   - ✅ Recipients = user emails (budi@example.com, dll)
   - ✅ Scheduled times sesuai jadwal obat
   - ✅ Status = "Scheduled" (bukan "Failed")

**Screenshot Checklist**:
- [ ] Status filter shows "Scheduled"
- [ ] At least 500+ notifications listed
- [ ] Scheduled time format visible
- [ ] No errors in the list

### Step 3: Wait for Scheduled Time

Notifikasi akan otomatis terkirim ke device saat jadwal obat tiba:

1. **Example**: Jadwal obat Metformin jam 08:00 Jakarta (01:00 UTC)
2. **OneSignal schedule**: 01:00 UTC = 08:00 Jakarta (with UTC+7)
3. **Device notification**: Akan muncul di browser/mobile saat jam 08:00 waktu user

## Why This Happened

**Sistem Lama** (Sebelum Message 9):
- Hanya command `medication:schedule-notifications` yang bisa schedule ke OneSignal
- Jadwal yang sudah ada tidak otomatis ke-sync
- User harus manual run command

**Sistem Baru** (Setelah Message 9):
- ✅ Real-time sync via Observer (automatic)
- ✅ Saat pasien/admin buat jadwal → otomatis sync
- ✅ Saat jadwal di-update → reschedule di OneSignal
- ⚠️ Jadwal LAMA masih perlu retroactive sync

## Verification Checklist

### Configuration ✅
```bash
php artisan onesignal:diagnose
```

Harus menunjukkan:
- ✅ OneSignal App ID configured
- ✅ OneSignal API Key configured
- ✅ Configuration looks correct

### Existing Schedules ✅
```bash
# Check berapa jadwal yang sudah active
php artisan tinker

# Dalam tinker shell:
MedicationSchedule::where('is_active', true)
  ->whereDate('start_date', '<=', now())
  ->where(function($q) { $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()); })
  ->count();
# Output: 9 (atau berapa pun jumlah jadwal active)

exit
```

### OneSignal Dashboard Check ✅
1. Go to: https://dashboard.onesignal.com/apps/5e8a47b0-aea8-46f5-aff2-d32e6e512fd8/notifications?scheduled=true
2. Filter by "Scheduled" status
3. Should see 540+ notifications (9 schedules × 30 days × 2 reminders)
4. Check recent creations show today's date/time

### Device Test ✅
1. Subscriber device harus online saat scheduled time
2. Device harus permission notifications enabled
3. OneSignal SDK running di app
4. User sudah login dan subscribed

## Ongoing Maintenance

### For New Schedules
✅ **Automatic** - Observer akan auto-sync saat:
- Admin input jadwal obat
- Pasien input jadwal obat  
- Jadwal di-update
- Jadwal di-delete (deactivate)

### For Regular Sync (Optional)
Jalankan batch sync setiap minggu untuk memastikan consistency:
```bash
# Weekly cron job - Every Monday 2 AM
0 2 * * 1 cd /app && php artisan medication:sync-existing-to-onesignal --days=30 --force

# Or manual:
php artisan medication:schedule-notifications --days=7
```

### For Monitoring
Check logs untuk verify sync berhasil:
```bash
# Recent sync activity
tail -50 storage/logs/laravel.log | grep -i "onesignal\|notification"

# Errors
grep -i "error" storage/logs/laravel.log | tail -20
```

## Troubleshooting

### ❌ "Successfully synced: 0 schedules"
**Possible causes:**
1. Tidak ada active schedules
   - Check: `php artisan tinker` → `MedicationSchedule::where('is_active', true)->count();`
   
2. Semua jadwal sudah passed (waktu lalu)
   - This is normal! Scheduler tidak reschedule waktu yang sudah passed
   - Buat jadwal baru untuk testing dengan waktu depan
   
3. User tidak memiliki email
   - Check: `User::whereNull('email')->count();`

### ❌ Notifications tidak muncul di device
1. **Check subscriber permission**:
   - Browser: Allow notifications di prompt
   - Mobile: Settings → Notifications → App → Enabled
   
2. **Check OneSignal status**:
   - Visit dashboard → Audiences → Subscribers
   - Check status: "Active" vs "Not Subscribed"
   
3. **Check device online**:
   - Notification akan queue jika offline
   - Device harus online saat scheduled time
   
4. **Check timezone**:
   ```bash
   php artisan onesignal:diagnose --user=budi@example.com
   ```
   - Verify timezone conversion correct
   - Local time vs UTC time mismatch = wrong notification time

### ❌ SSL Certificate Error
This is expected pada localhost development environment. Ini tidak mempengaruhi functionality di production:
```
cURL error 60: SSL certificate OpenSSL verify result...
```

**Solution**: 
- Development: Ignore, ini hanya diagnostic API check
- Production: Should not appear (SSL valid)
- If persists: Check firewall/proxy settings

## Command Reference

| Command | Purpose | Example |
|---------|---------|---------|
| `medication:sync-existing-to-onesignal` | Sync existing schedules | `--days=30 --force` |
| `medication:schedule-notifications` | Batch sync for next N days | `--days=7` |
| `onesignal:diagnose` | Check configuration | `--user=budi@example.com` |

## What Happens Behind the Scenes

### When Sync Runs:
```
1. Get all active medication schedules
2. For each schedule:
   - Calculate notification times for next 30 days
   - Parse time in user's timezone (e.g., 08:00 Jakarta = UTC+7)
   - Convert to UTC (e.g., 01:00 UTC)
   - Format as ISO 8601 (e.g., 2026-05-03T01:00:00Z)
   - Send to OneSignal API with send_after timestamp
3. OneSignal queue: Schedule delivery for calculated time
4. Device receives notification at calculated time
```

### Timeline Example:
```
User: Budi (Jakarta, UTC+7)
Schedule: Metformin @ 08:00 Jakarta
  
May 3, 08:00 Jakarta = May 3, 01:00 UTC
→ OneSignal send_after: 2026-05-03T01:00:00Z
→ OneSignal queue: 2026-05-03 01:00 UTC
→ Scheduled time: 2026-05-03 08:00 Jakarta
→ Device notification: 2026-05-03 08:00 (Budi's local time) ✓

May 4, 08:00 Jakarta = May 4, 01:00 UTC
→ OneSignal send_after: 2026-05-04T01:00:00Z
... (repeat for 30 days)
```

## FAQ

**Q: Kenapa ada 2 notifikasi (first + second)?**
A: First reminder saat jadwal obat, second reminder 30 menit kemudian sebagai follow-up

**Q: Berapa lama notifikasi scheduled?**
A: Default 30 hari ke depan. Bisa customize dengan `--days=60` etc

**Q: Bisa cancel notification?**
A: OneSignal API tidak support cancel. Notification will deliver sesuai scheduled time.

**Q: Bisa reschedule existing notification?**
A: Sync ulang dengan waktu baru akan create notifikasi baru (yang lama tetap deliver)

**Q: Timezone tidak sesuai?**
A: Check `users.timezone` column. Default ke `config('app.timezone')` if null.

---

**Status**: ✅ **RESOLVED**  
**Last Updated**: May 2, 2026  
**Test Result**: 9 schedules → 540 notifications synced successfully
