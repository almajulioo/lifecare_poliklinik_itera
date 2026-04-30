<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\MedicationSchedule;
use App\Models\NotificationLog;
use App\Notifications\MedicationReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendMedicationReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'medication:send-reminders {--check-interval=5}';

    /**
     * The description of the console command.
     */
    protected $description = 'Send OneSignal medication reminders to users when it\'s time to take their medicine';

    /**
     * Waktu untuk reminder kedua (30 menit setelah jadwal)
     */
    const SECOND_REMINDER_MINUTES = 30;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = (int) $this->option('check-interval');

        $this->info("Starting medication reminders sender (checking every {$interval}min)...");

        // Get all active users
        $users = User::whereIn('role_user', ['user', 'mahasiswa', 'pegawai', 'pasien', 'patient'])
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No users found');
            return;
        }

        $this->info("Found " . $users->count() . " users");

        $firstRemindersCount = 0;
        $secondRemindersCount = 0;

        foreach ($users as $user) {
            try {
                // Cek preferences & DND
                if (!$this->shouldSendNotification($user)) {
                    continue;
                }

                // SEND FIRST REMINDERS
                $firstReminders = $this->getFirstReminders($user);
                foreach ($firstReminders as $medication) {
                    try {
                        $this->sendFirstReminder($user, $medication);
                        $firstRemindersCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to send first reminder', [
                            'user_id' => $user->id,
                            'medication_schedule_id' => $medication->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // SEND SECOND REMINDERS (after 30 minutes)
                $secondReminders = $this->getSecondReminders($user);
                foreach ($secondReminders as $item) {
                    try {
                        $this->sendSecondReminder($user, $item);
                        $secondRemindersCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to send second reminder', [
                            'user_id' => $user->id,
                            'notification_log_id' => $item['notification_log_id'],
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error processing reminders for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("✅ First reminders sent: {$firstRemindersCount}");
        $this->info("✅ Second reminders sent: {$secondRemindersCount}");
        $this->info("Total reminders: " . ($firstRemindersCount + $secondRemindersCount));
    }

    /**
     * Get medications yang jadwalnya sudah sampai (first reminder)
     */
    private function getFirstReminders(User $user)
    {
        $now = now();

        $schedules = MedicationSchedule::with(['medicine', 'logs' => function($q) {
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

        // Filter: Semua jadwal aktif hari ini yang belum diproses
        return $schedules->filter(function($schedule) use ($user) {
            // Filter: belum kirim/jadwalkan notifikasi hari ini
            return !NotificationLog::where('user_id', $user->id)
                ->where('medication_schedule_id', $schedule->id)
                ->whereDate('scheduled_time', today())
                ->exists();
        });
    }

    /**
     * Get medications untuk second reminder (30+ min setelah jadwal)
     */
    private function getSecondReminders(User $user)
    {
        $now = now();

        return NotificationLog::join('medication_logs', function($join) {
            $join->on('notification_logs.medication_schedule_id', '=', 'medication_logs.medication_schedule_id')
                ->on('notification_logs.user_id', '=', 'medication_logs.user_id');
        })
        ->join('medication_schedules', 'notification_logs.medication_schedule_id', '=', 'medication_schedules.id')
        ->join('medicines', 'medication_schedules.medicine_id', '=', 'medicines.id')
        ->where('notification_logs.user_id', $user->id)
        ->where('notification_logs.reminder_number', 1)
        ->whereNull('notification_logs.second_reminder_sent_at')
        ->where('notification_logs.second_reminder_at', '<=', $now)
        ->where('medication_logs.status', '!=', 'taken')
        ->whereDate('notification_logs.scheduled_time', today())
        ->select(
            'notification_logs.id as notification_log_id',
            'notification_logs.medication_schedule_id',
            'medication_schedules.time',
            'medicines.name as medicine_name',
            'medicines.dose as medicine_dose',
            'medicines.unit as medicine_unit',
            'medication_schedules.id as schedule_id'
        )
        ->orderBy('notification_logs.scheduled_time')
        ->get()
        ->map(function($item) {
            return [
                'notification_log_id' => $item->notification_log_id,
                'medication_schedule_id' => $item->medication_schedule_id,
                'schedule_id' => $item->schedule_id,
                'medicine_name' => $item->medicine_name,
                'medicine_dose' => $item->medicine_dose . ' ' . ($item->medicine_unit ?? ''),
                'time' => $item->time,
            ];
        });
    }

    /**
     * Kirim first reminder via OneSignal
     */
    private function sendFirstReminder(User $user, MedicationSchedule $schedule)
    {
        [$hour, $minute] = explode(':', $schedule->time);
        $scheduledTime = Carbon::createFromTime($hour, $minute);
        
        $medicineDose = $schedule->medicine->dose . ' ' . ($schedule->medicine->unit ?? '');

        Notification::send($user, new MedicationReminderNotification(
            $schedule->medicine->name,
            $medicineDose,
            $schedule->time,
            $schedule->id,
            'first',
            $scheduledTime->toDateTimeString()
        ));

        // Catat di NotificationLog
        NotificationLog::create([
            'user_id' => $user->id,
            'medication_schedule_id' => $schedule->id,
            'scheduled_time' => $scheduledTime->toDateTimeString(),
            'sent_at' => now(),
            'status' => 'sent',
            'notification_type' => 'onesignal',
            'reminder_number' => 1,
            'second_reminder_at' => $scheduledTime->copy()->addMinutes(self::SECOND_REMINDER_MINUTES),
        ]);

        Log::info('First reminder sent via OneSignal', [
            'user_id' => $user->id,
            'medication_schedule_id' => $schedule->id,
            'medicine' => $schedule->medicine->name,
        ]);
    }

    /**
     * Kirim second reminder via OneSignal
     */
    private function sendSecondReminder(User $user, array $item)
    {
        $schedule = MedicationSchedule::with('medicine')->find($item['schedule_id']);
        if (!$schedule) {
            Log::warning('Schedule not found', ['schedule_id' => $item['schedule_id']]);
            return;
        }

        Notification::send($user, new MedicationReminderNotification(
            $schedule->medicine->name,
            $item['medicine_dose'],
            $item['time'],
            $item['medication_schedule_id'],
            'second',
            now()->toDateTimeString()
        ));

        // Update NotificationLog
        $notifLog = NotificationLog::find($item['notification_log_id']);
        if ($notifLog) {
            $notifLog->update([
                'second_reminder_sent_at' => now(),
                'notification_type' => 'onesignal',
            ]);
        }

        Log::info('Second reminder sent via OneSignal', [
            'user_id' => $user->id,
            'medication_schedule_id' => $item['medication_schedule_id'],
            'medicine' => $schedule->medicine->name,
        ]);
    }

    /**
     * Check apakah harus kirim notifikasi (cek preferences & DND)
     */
    private function shouldSendNotification(User $user): bool
    {
        $prefs = json_decode($user->notification_preferences ?? '{}', true);

        $enabled = $prefs['enabled'] ?? true;
        $dndStart = $prefs['dnd_start'] ?? '22:00';
        $dndEnd = $prefs['dnd_end'] ?? '08:00';

        if (!$enabled) {
            return false;
        }

        $now = now();
        $currentTime = $now->format('H:i');

        $isDnd = false;
        if ($dndStart > $dndEnd) {
            $isDnd = $currentTime >= $dndStart || $currentTime < $dndEnd;
        } else {
            $isDnd = $currentTime >= $dndStart && $currentTime < $dndEnd;
        }

        return !$isDnd;
    }
}
