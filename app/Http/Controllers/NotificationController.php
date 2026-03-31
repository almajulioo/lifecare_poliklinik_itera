<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicationSchedule;
use App\Models\NotificationLog;
use App\Models\MedicationLog;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get today's notification times (schedules that should trigger notifications)
     * Returns list of medication times for today and tomorrow
     */
    public function getNotificationTimes(Request $request)
    {
        $user = $request->user();
        $today = today();
        $tomorrow = $today->addDay();

        // TODAY'S SCHEDULES
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

        // Get already taken today
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
            'user_timezone' => $user->timezone ?? 'UTC',
            'current_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Mark notification as sent (for tracking)
     */
    public function markNotificationSent(Request $request)
    {
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
            'scheduled_time' => 'required|date',
            'notification_type' => 'required|in:browser,sound,both',
        ]);

        $user = $request->user();

        // Check if already tracked today
        $existing = NotificationLog::where('user_id', $user->id)
            ->where('medication_schedule_id', $validated['medication_schedule_id'])
            ->whereDate('scheduled_time', today())
            ->first();

        if ($existing) {
            // Update if already exists
            $existing->update([
                'sent_at' => now(),
                'status' => 'sent',
                'notification_type' => $validated['notification_type'],
            ]);
            $notifLog = $existing;
        } else {
            // Create new notification log
            $notifLog = NotificationLog::create([
                'user_id' => $user->id,
                'medication_schedule_id' => $validated['medication_schedule_id'],
                'scheduled_time' => $validated['scheduled_time'],
                'sent_at' => now(),
                'status' => 'sent',
                'notification_type' => $validated['notification_type'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification tracked',
            'notification_log_id' => $notifLog->id,
        ]);
    }

    /**
     * Get user notification preferences
     */
    public function getPreferences(Request $request)
    {
        $user = $request->user();

        // Get from JSON field if exists, or default
        $prefs = json_decode($user->notification_preferences ?? '{}', true);

        $defaults = [
            'enabled' => true,
            'dnd_start' => '22:00',  // Do not disturb start
            'dnd_end' => '08:00',    // Do not disturb end
            'sound_enabled' => true,
            'advance_minutes' => 0,  // How many minutes before to notify (0 = on time)
            'vibration_enabled' => true,
            'timezone' => $user->timezone ?? 'UTC',
        ];

        return response()->json([
            'success' => true,
            'preferences' => array_merge($defaults, $prefs),
        ]);
    }

    /**
     * Save user notification preferences
     */
    public function savePreferences(Request $request)
    {
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

    /**
     * Check if should notify based on preferences and current time
     */
    public function shouldNotify(Request $request)
    {
        $user = $request->user();
        $prefs = json_decode($user->notification_preferences ?? '{}', true);

        // Defaults
        $enabled = $prefs['enabled'] ?? true;
        $dndStart = $prefs['dnd_start'] ?? '22:00';
        $dndEnd = $prefs['dnd_end'] ?? '08:00';

        if (!$enabled) {
            return response()->json(['should_notify' => false, 'reason' => 'notifications_disabled']);
        }

        // Check do-not-disturb window
        $now = now();
        $currentTime = $now->format('H:i');

        // Handle overnight DND (e.g., 22:00 - 08:00)
        $isDnd = false;
        if ($dndStart > $dndEnd) {
            // Overnight range
            $isDnd = $currentTime >= $dndStart || $currentTime < $dndEnd;
        } else {
            // Same-day range
            $isDnd = $currentTime >= $dndStart && $currentTime < $dndEnd;
        }

        if ($isDnd) {
            return response()->json(['should_notify' => false, 'reason' => 'do_not_disturb']);
        }

        return response()->json(['should_notify' => true, 'reason' => 'ok']);
    }

    /**
     * Mark notification as snoozed
     */
    public function snoozeNotification(Request $request)
    {
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
            'snooze_minutes' => 'required|integer|min:5|max:120',
        ]);

        $user = $request->user();

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

    /**
     * Dismiss notification (don't remind me today)
     */
    public function dismissNotification(Request $request)
    {
        $validated = $request->validate([
            'medication_schedule_id' => 'required|exists:medication_schedules,id',
        ]);

        $user = $request->user();

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
}
