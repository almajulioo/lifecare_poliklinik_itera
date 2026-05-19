<?php

// Kontrol untuk mengelola notifikasi dan preferensi user
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicationSchedule;
use App\Models\NotificationLog;
use App\Models\MedicationLog;
use App\Models\User;
use App\Notifications\MedicationReminderNotification;
use App\Services\OneSignalConfigService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    /**
     * Kirim notifikasi percobaan (Test Notification) via OneSignal.
     */
    public function sendTestNotification(Request $request)
    {
        $user = $request->user();

        try {
            // Validate OneSignal is configured
            if (!OneSignalConfigService::isConfigured()) {
                $status = OneSignalConfigService::getConfigurationStatus();
                Log::error('OneSignal configuration missing', $status);

                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi OneSignal tidak lengkap. Silakan hubungi administrator untuk mengatur ONESIGNAL_APP_ID dan ONESIGNAL_REST_API_KEY di file .env',
                ], 500);
            }

            // Validate user data
            $userValidation = OneSignalConfigService::validateUserForNotification($user);
            if (!$userValidation['valid']) {
                Log::error('User validation failed for notification', $userValidation);

                return response()->json([
                    'success' => false,
                    'message' => 'Validasi pengguna gagal: ' . implode(', ', $userValidation['errors']),
                ], 422);
            }

            Log::info('Sending test notification', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Send test notification
            Notification::send($user, new MedicationReminderNotification(
                'Obat Percobaan',
                '1 Tablet',
                now()->format('H:i'),
                0, // ID 0 untuk test
                'confirmation' // Gunakan template konfirmasi agar terlihat seperti test sukses
            ));

            Log::info('Test notification sent successfully', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now()->toDateTimeString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi percobaan telah dikirim ke OneSignal. Cek HP atau browser Anda dalam beberapa detik.',
                'user_email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi percobaan OneSignal', [
                'user_id' => $user->id,
                'user_email' => $user->email ?? 'N/A',
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            $message = 'Gagal mengirim notifikasi';
            if (config('app.debug')) {
                $message .= ': ' . $e->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // Waktu untuk reminder kedua (30 menit setelah jadwal)
    const SECOND_REMINDER_MINUTES = 30;
    

    // Ambil waktu notifikasi hari ini dan besok (jadwal yang perlu notifikasi)
    public function getNotificationTimes(Request $request)
    {
        $user = $request->user();
        $today = today();
        $tomorrow = $today->addDay();

        // Ambil jadwal obat hari ini
        $todaySchedules = MedicationSchedule::with(['medicine'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->where(function($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->orderBy('time')
            ->get();

        // TOMORROW'S SCHEDULES
        $tomorrowSchedules = MedicationSchedule::with(['medicine'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', $tomorrow)
            ->where(function($q) use ($tomorrow) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $tomorrow);
            })
            ->orderBy('time')
            ->limit(5)
            ->get();

        // Ambil obat yang sudah diminum hari ini
        $takenToday = MedicationLog::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->where('status', 'taken')
            ->pluck('medication_schedule_id')
            ->toArray();

        // Build notification times
        $todayNotifications = $todaySchedules->map(function($schedule) use ($takenToday, $today) {
            $alreadyTaken = in_array($schedule->id, $takenToday);
            
            // Convert time to full datetime for today
            [$hour, $minute] = explode(':', $schedule->time);
            $scheduledTime = Carbon::createFromTime($hour, $minute);

            return [
                'id' => $schedule->id,
                'medicine_name' => $schedule->medicine->name,
                'medicine_dose' => $schedule->medicine->dose . ' ' . ($schedule->medicine->unit ?? ''),
                'medicine_icon' => '💊',
                'time' => $schedule->time,
                'scheduled_datetime' => $scheduledTime->toIso8601String(),
                'already_taken' => $alreadyTaken,
                'should_notify' => !$alreadyTaken,
                'date' => $today->toDateString(),
            ];
        })->filter(function($item) {
            return $item['should_notify'];
        })->values();

        $tomorrowNotifications = $tomorrowSchedules->map(function($schedule) use ($tomorrow) {
            [$hour, $minute] = explode(':', $schedule->time);
            $scheduledTime = Carbon::createFromTime($hour, $minute);

            return [
                'id' => $schedule->id,
                'medicine_name' => $schedule->medicine->name,
                'medicine_dose' => $schedule->medicine->dose . ' ' . ($schedule->medicine->unit ?? ''),
                'medicine_icon' => '💊',
                'time' => $schedule->time,
                'scheduled_datetime' => $scheduledTime->copy()->setDate($tomorrow->year, $tomorrow->month, $tomorrow->day)->toIso8601String(),
                'date' => $tomorrow->toDateString(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'today' => $todayNotifications,
            'tomorrow' => $tomorrowNotifications,
            'user_timezone' => $user->timezone ?? 'Asia/Jakarta',
            'current_time' => now()->toIso8601String(),
        ]);
    }

    // Catat notifikasi yang sudah dikirim (untuk tracking) + support FCM
    public function markNotificationSent(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
            'scheduled_time' => 'required|date',
            'notification_type' => 'required|in:browser,sound,both',
        ]);

        $user = $request->user();

        // Cek apakah sudah tercatat hari ini
        $existing = NotificationLog::where('user_id', $user->id)
            ->where('medication_schedule_id', $validated['medication_schedule_id'])
            ->whereDate('scheduled_time', today())
            ->first();

        // Hitung waktu reminder kedua
        $scheduledTime = Carbon::parse($validated['scheduled_time']);
        $secondReminderAt = $scheduledTime->copy()->addMinutes(self::SECOND_REMINDER_MINUTES);

        if ($existing) {
            // Update jika sudah ada
            $existing->update([
                'sent_at' => now(),
                'status' => 'sent',
                'notification_type' => $validated['notification_type'],
                'reminder_number' => 1,
                'second_reminder_at' => $secondReminderAt,
            ]);
            $notifLog = $existing;
        } else {
            // Buat notifikasi log baru
            $notifLog = NotificationLog::create([
                'user_id' => $user->id,
                'medication_schedule_id' => $validated['medication_schedule_id'],
                'scheduled_time' => $validated['scheduled_time'],
                'sent_at' => now(),
                'status' => 'sent',
                'notification_type' => $validated['notification_type'],
                'reminder_number' => 1,
                'second_reminder_at' => $secondReminderAt,
            ]);
        }

        Log::info('Notification tracked', [
            'user_id' => $user->id,
            'medication_schedule_id' => $validated['medication_schedule_id'],
            'notification_type' => $validated['notification_type'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification tracked',
            'notification_log_id' => $notifLog->id,
        ]);
    }

    // Ambil preferensi notifikasi user
    public function getPreferences(Request $request)
    {
        $user = $request->user();

        // Ambil dari JSON field atau default
        $prefs = json_decode($user->notification_preferences ?? '{}', true);

        // Default preferences
        $defaults = [
            'enabled' => true,
            'dnd_start' => '22:00',
            'dnd_end' => '08:00',
            'sound_enabled' => true,
            'advance_minutes' => 0,
            'vibration_enabled' => true,
            'timezone' => $user->timezone ?? 'Asia/Jakarta',
        ];

        return response()->json([
            'success' => true,
            'preferences' => array_merge($defaults, $prefs),
        ]);
    }

    // Simpan preferensi notifikasi user
    public function savePreferences(Request $request)
    {
        // Validasi input preferensi
        $validated = $request->validate([
            'enabled' => 'boolean',
            'dnd_start' => 'required|date_format:H:i',
            'dnd_end' => 'required|date_format:H:i',
            'sound_enabled' => 'boolean',
            'advance_minutes' => 'integer|min:0|max:60',
            'vibration_enabled' => 'boolean',
            'timezone' => 'nullable|timezone',
        ]);

        $user = $request->user();
        // Simpan preferensi ke database
        $user->update([
            'notification_preferences' => json_encode($validated),
            'timezone' => $validated['timezone'] ?? $user->timezone,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preferences saved',
            'preferences' => $validated,
        ]);
    }

    // Cek apakah harus kirim notifikasi (cek preferensi & DND)
    public function shouldNotify(Request $request)
    {
        $user = $request->user();
        // Ambil preferensi dari JSON
        $prefs = json_decode($user->notification_preferences ?? '{}', true);

        // Default values
        $enabled = $prefs['enabled'] ?? true;
        $dndStart = $prefs['dnd_start'] ?? '22:00';
        $dndEnd = $prefs['dnd_end'] ?? '08:00';

        if (!$enabled) {
            return response()->json(['should_notify' => false, 'reason' => 'notifications_disabled']);
        }

        // Cek jendela do-not-disturb
        $now = now();
        $currentTime = $now->format('H:i');

        // Handle DND overnight (e.g., 22:00 - 08:00)
        $isDnd = false;
        if ($dndStart > $dndEnd) {
            // Range overnight
            $isDnd = $currentTime >= $dndStart || $currentTime < $dndEnd;
        } else {
            // Range same-day
            $isDnd = $currentTime >= $dndStart && $currentTime < $dndEnd;
        }

        if ($isDnd) {
            return response()->json(['should_notify' => false, 'reason' => 'do_not_disturb']);
        }

        return response()->json(['should_notify' => true, 'reason' => 'ok']);
    }

    // Tunda/snooze notifikasi obat
    public function snoozeNotification(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
            'snooze_minutes' => 'required|integer|min:5|max:120',
        ]);

        $user = $request->user();

        // Update status notifikasi menjadi snoozed
        $notifLog = NotificationLog::where('user_id', $user->id)
            ->where('medication_schedule_id', $validated['medication_schedule_id'])
            ->whereDate('scheduled_time', today())
            ->first();

        if ($notifLog) {
            $notifLog->update([
                'status' => 'snoozed',
                'snooze_minutes' => $validated['snooze_minutes'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification snoozed',
            'snooze_until' => now()->addMinutes($validated['snooze_minutes'])->toIso8601String(),
        ]);
    }

    // Ambil reminder kedua yang pending (obat belum dikonfirmasi dalam 30 menit) + kirim FCM
    public function getSecondReminders(Request $request)
    {
        $user = $request->user();
        $now = now();

        // Query reminder kedua yang pending maupun belum dikirim
        $secondReminders = NotificationLog::join('medication_logs', function($join) {
            $join->on('notification_logs.medication_schedule_id', '=', 'medication_logs.medication_schedule_id')
                ->on('notification_logs.user_id', '=', 'medication_logs.user_id');
        })
        ->join('medication_schedules', function($join) {
            $join->on('notification_logs.medication_schedule_id', '=', 'medication_schedules.id');
        })
        ->join('medicines', 'medication_schedules.medicine_id', '=', 'medicines.id')
        ->where('notification_logs.user_id', $user->id)
        ->where('notification_logs.reminder_number', 1)
        ->whereNull('notification_logs.second_reminder_sent_at')
        ->where('notification_logs.second_reminder_at', '<=', $now)
        ->where('medication_logs.status', '!=', 'taken')
        ->whereDate('notification_logs.scheduled_time', today())
        ->select(
            'notification_logs.id',
            'notification_logs.medication_schedule_id',
            'medication_schedules.time',
            'medicines.name as medicine_name',
            'medicines.dose as medicine_dose',
            'medicines.unit as medicine_unit',
            'notification_logs.scheduled_time',
            'medication_schedules.id as schedule_id'
        )
        ->orderBy('notification_logs.scheduled_time')
        ->get()
        ->map(function($item) {
            return [
                'notification_log_id' => $item->id,
                'medication_schedule_id' => $item->medication_schedule_id,
                'medicine_name' => $item->medicine_name,
                'medicine_dose' => $item->medicine_dose . ' ' . ($item->medicine_unit ?? ''),
                'medicine_icon' => '💊',
                'time' => $item->time,
                'scheduled_datetime' => $item->scheduled_time->toIso8601String(),
                'reminder_type' => 'second',
                'date' => today()->toDateString(),
            ];
        });

        return response()->json([
            'success' => true,
            'second_reminders' => $secondReminders,
            'count' => $secondReminders->count(),
            'user_timezone' => $user->timezone ?? 'UTC',
            'current_time' => now()->toIso8601String(),
        ]);
    }

    // Catat reminder kedua yang sudah dikirim + track FCM
    public function markSecondReminderSent(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'notification_log_id' => 'required|exists:notification_logs,id',
            'notification_type' => 'required|in:browser,sound,both',
        ]);

        $user = $request->user();

        // Update notification log
        $notifLog = NotificationLog::where('user_id', $user->id)
            ->where('id', $validated['notification_log_id'])
            ->first();

        if (!$notifLog) {
            return response()->json([
                'success' => false,
                'message' => 'Notification log not found',
            ], 404);
        }

        // Update waktu reminder kedua dikirim
        $notifLog->update([
            'second_reminder_sent_at' => now(),
            'notification_type' => $validated['notification_type'],
        ]);

        Log::info('Second reminder tracked', [
            'user_id' => $user->id,
            'notification_log_id' => $validated['notification_log_id'],
            'notification_type' => $validated['notification_type'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Second reminder tracked',
        ]);
    }

    // Abaikan notifikasi (jangan ingatkan hari ini)
    public function dismissNotification(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
        ]);

        $user = $request->user();

        // Cari atau buat notifikasi log dengan status dismissed
        $notifLog = NotificationLog::where('user_id', $user->id)
            ->where('medication_schedule_id', $validated['medication_schedule_id'])
            ->whereDate('scheduled_time', today())
            ->first();

        if ($notifLog) {
            $notifLog->update(['status' => 'dismissed']);
        } else {
            NotificationLog::create([
                'user_id' => $user->id,
                'medication_schedule_id' => $validated['medication_schedule_id'],
                'scheduled_time' => now(),
                'status' => 'dismissed',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification dismissed',
        ]);
    }

    // Ambil obat yang harus diminum sekarang (untuk dashboard reminder) + kirim FCM
    public function getDueMedications(Request $request)
    {
        $user = $request->user();
        $now = now();

        // Ambil jadwal obat hari ini
        $todaySchedules = MedicationSchedule::with(['medicine', 'logs' => function($q) {
                $q->whereDate('updated_at', today())
                  ->orWhereDate('taken_at', today());
            }])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', today())
            ->where(function($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            })
            ->get();

        // Filter obat yang waktu minumnya sudah melewati waktu sekarang
        $dueMedications = $todaySchedules->filter(function($schedule) use ($now, $user) {
            [$hour, $minute] = explode(':', $schedule->time);
            $scheduledTime = Carbon::createFromTime($hour, $minute);
            
            // Hanya tampilkan jika waktu sekarang > waktu jadwal (sudah melewati)
            if (! $now->gt($scheduledTime)) {
                return false;
            }

            return ! $this->isScheduleSnoozed($user->id, $schedule->id, $now);
        })
        ->filter(function($schedule) {
            // Filter hanya obat yang belum diminum
            $log = $schedule->logs->first();
            return !$log || $log->status != 'taken';
        })
        ->map(function($schedule) use ($user) {
            [$hour, $minute] = explode(':', $schedule->time);
            $scheduledTime = Carbon::createFromTime($hour, $minute);
            
            $log = $schedule->logs->first();
            
            return [
                'medication_schedule_id' => $schedule->id,
                'medicine_name' => $schedule->medicine->name,
                'medicine_dose' => $schedule->medicine->dose . ' ' . ($schedule->medicine->unit ?? ''),
                'time' => $schedule->time,
                'scheduled_datetime' => $scheduledTime->toIso8601String(),
                'status' => $log?->status ?? 'pending',
                'reminder_type' => 'dashboard',
            ];
        })
        ->values();

        return response()->json([
            'success' => true,
            'due_medications' => $dueMedications,
            'count' => $dueMedications->count(),
            'current_time' => $now->toIso8601String(),
        ]);
    }

    private function isScheduleSnoozed(int $userId, int $scheduleId, Carbon $now): bool
    {
        $notifLog = NotificationLog::where('user_id', $userId)
            ->where('medication_schedule_id', $scheduleId)
            ->whereDate('scheduled_time', today())
            ->latest('id')
            ->first();

        if (! $notifLog || $notifLog->status != 'snoozed' || ! $notifLog->snooze_until) {
            return false;
        }

        return Carbon::parse($notifLog->snooze_until)->gt($now);
    }

    // Tangani tombol "Nanti" - snooze reminder 5 menit
    public function snoozeReminderDashboard(Request $request)
    {
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
            'snooze_minutes' => 'required|integer|in:5,10,15',
        ]);

        $user = $request->user();
        $snoozeUntil = now()->addMinutes($validated['snooze_minutes']);

        // Simpan ke dalam NotificationLog
        $notifLog = NotificationLog::where('user_id', $user->id)
            ->where('medication_schedule_id', $validated['medication_schedule_id'])
            ->whereDate('scheduled_time', today())
            ->first();

        if ($notifLog) {
            $notifLog->update([
                'status' => 'snoozed',
                'snooze_minutes' => $validated['snooze_minutes'],
                'snooze_until' => $snoozeUntil,
            ]);
        } else {
            // Buat record baru jika tidak ada
            NotificationLog::create([
                'user_id' => $user->id,
                'medication_schedule_id' => $validated['medication_schedule_id'],
                'scheduled_time' => now(),
                'status' => 'snoozed',
                'snooze_minutes' => $validated['snooze_minutes'],
                'snooze_until' => $snoozeUntil,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reminder snoozed for ' . $validated['snooze_minutes'] . ' minutes',
            'snooze_until' => $snoozeUntil->toIso8601String(),
        ]);
    }

    // Ambil reminder pending untuk dashboard (termasuk yang di-snooze)
    public function getPendingReminders(Request $request)
    {
        $user = $request->user();
        $now = now();

        // Ambil semua reminder pending yang waktu snoozenya sudah berlalu
        $pendingReminders = NotificationLog::where('user_id', $user->id)
            ->whereDate('scheduled_time', today())
            ->where(function($q) use ($now) {
                $q->where('status', 'snoozed')
                  ->where(function($subQ) use ($now) {
                      $subQ->whereNull('snooze_until')
                           ->orWhere('snooze_until', '<=', $now);
                  });
            })
            ->orWhere(function($q) use ($user, $now) {
                $q->where('user_id', $user->id)
                  ->whereDate('scheduled_time', today())
                  ->where('status', 'pending');
            })
            ->with(['medicationSchedule.medicine'])
            ->orderBy('scheduled_time')
            ->get();

        $reminders = $pendingReminders->map(function($notif) {
            $schedule = $notif->medicationSchedule;
            [$hour, $minute] = explode(':', $schedule->time);
            $scheduledTime = Carbon::createFromTime($hour, $minute);

            return [
                'medication_schedule_id' => $schedule->id,
                'medicine_name' => $schedule->medicine->name,
                'medicine_dose' => $schedule->medicine->dose . ' ' . ($schedule->medicine->unit ?? ''),
                'time' => $schedule->time,
                'scheduled_datetime' => $scheduledTime->toIso8601String(),
                'status' => $notif->status,
                'snooze_minutes' => $notif->snooze_minutes,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'pending_reminders' => $reminders,
            'count' => $reminders->count(),
        ]);
    }

    // Handle notification actions (click, dismiss, etc.)
    public function notificationAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:clicked,dismissed,snoozed',
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
            'timestamp' => 'required|date_format:Y-m-d\TH:i:s.u\Z',
        ]);

        $user = $request->user();
        $scheduleId = $validated['medication_schedule_id'];
        $action = $validated['action'];

        // Find notification log
        $notifLog = NotificationLog::where('user_id', $user->id)
            ->where('medication_schedule_id', $scheduleId)
            ->whereDate('scheduled_time', today())
            ->first();

        if (!$notifLog) {
            return response()->json([
                'success' => false,
                'message' => 'Notification log not found'
            ], 404);
        }

        // Update notification log based on action
        switch ($action) {
            case 'clicked':
                $notifLog->update([
                    'status' => 'clicked',
                    'clicked_at' => now(),
                ]);
                Log::info("User {$user->id} clicked notification for schedule {$scheduleId}");
                break;

            case 'dismissed':
                $notifLog->update([
                    'status' => 'dismissed',
                    'dismissed_at' => now(),
                ]);
                Log::info("User {$user->id} dismissed notification for schedule {$scheduleId}");
                break;

            case 'snoozed':
                $notifLog->update([
                    'status' => 'snoozed',
                    'snooze_minutes' => 5,
                    'snooze_until' => now()->addMinutes(5),
                ]);
                Log::info("User {$user->id} snoozed notification for schedule {$scheduleId}");
                break;
        }

        return response()->json([
            'success' => true,
            'message' => "Notification {$action} recorded",
            'action' => $action,
        ]);
    }
}
