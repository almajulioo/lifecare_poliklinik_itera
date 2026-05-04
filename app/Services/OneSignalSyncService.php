<?php

namespace App\Services;

use App\Models\User;
use App\Models\MedicationSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk sinkronisasi jadwal obat dengan OneSignal Scheduled Messages
 * Menangani: create, update, delete jadwal dan otomatis sync ke OneSignal
 */
class OneSignalSyncService
{
    const NOTIFICATION_LOOKAHEAD_DAYS = 30; // Jadwalkan notifikasi untuk 30 hari ke depan
    const SECOND_REMINDER_DELAY_MINUTES = 30;
    const ONESIGNAL_API_URL = 'https://onesignal.com/api/v1/notifications';

    /**
     * Sinkronisasi jadwal baru/update ke OneSignal
     * Dipanggil dari observer saat schedule created/updated, atau manual sync
     * @param int|null $lookaheadDays Berapa hari ke depan untuk schedule. Default: 30 hari
     */
    public function syncScheduleToOneSignal(MedicationSchedule $schedule, ?int $lookaheadDays = null): bool
    {
        $lookaheadDays = $lookaheadDays ?? self::NOTIFICATION_LOOKAHEAD_DAYS;

        try {
            $user = $schedule->user;
            if (!$user || !$user->email) {
                Log::warning('Cannot sync OneSignal: missing user or email', [
                    'schedule_id' => $schedule->id,
                ]);
                return false;
            }

            // Jika schedule sedang inactive, jangan schedule notifikasi
            if (!$schedule->is_active) {
                return true;
            }

            // Hitung rentang scheduling
            $now = now('UTC');
            $startDate = $now->copy()->startOfDay();
            $endDate = $startDate->copy()->addDays($lookaheadDays);

            $userTimezone = $user->timezone ?? config('app.timezone');
            $scheduledCount = 0;

            // Loop untuk setiap hari dalam rentang
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                // Skip jika schedule belum dimulai atau sudah berakhir
                if ($currentDate->format('Y-m-d') < $schedule->start_date->format('Y-m-d')) {
                    $currentDate->addDay();
                    continue;
                }
                if ($schedule->end_date && $currentDate->format('Y-m-d') > $schedule->end_date->format('Y-m-d')) {
                    break;
                }

                // Parse waktu notifikasi dalam timezone user, convert ke UTC
                $notificationTimeLocal = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $currentDate->format('Y-m-d') . ' ' . $schedule->time,
                    $userTimezone
                );
                $notificationTimeUtc = $notificationTimeLocal->copy()->setTimezone('UTC');

                // Hanya schedule jika waktu masih di masa depan
                if ($notificationTimeUtc > $now) {
                    // Schedule first reminder
                    if ($this->scheduleNotification(
                        $user,
                        $schedule,
                        $notificationTimeUtc,
                        'first',
                        $notificationTimeLocal
                    )) {
                        $scheduledCount++;
                    }

                    // Schedule second reminder 30 minutes later
                    $secondReminderTimeUtc = $notificationTimeUtc->copy()->addMinutes(self::SECOND_REMINDER_DELAY_MINUTES);
                    $secondReminderTimeLocal = $notificationTimeLocal->copy()->addMinutes(self::SECOND_REMINDER_DELAY_MINUTES);
                    if ($this->scheduleNotification(
                        $user,
                        $schedule,
                        $secondReminderTimeUtc,
                        'second',
                        $secondReminderTimeLocal
                    )) {
                        $scheduledCount++;
                    }
                }

                $currentDate->addDay();
            }

            Log::info('OneSignal sync completed for schedule', [
                'schedule_id' => $schedule->id,
                'user_id' => $user->id,
                'medicine' => $schedule->medicine->name,
                'scheduled_notifications' => $scheduledCount,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error syncing OneSignal for schedule', [
                'error' => $e->getMessage(),
                'schedule_id' => $schedule->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Deactivate notifikasi saat schedule dihapus atau dinonaktifkan
     * OneSignal tidak support cancel scheduled notifications langsung
     * Jadi kita mark sebagai deactivated dan lepas dari OneSignal
     */
    public function deactivateScheduleFromOneSignal(MedicationSchedule $schedule): bool
    {
        try {
            // OneSignal API tidak support delete scheduled notification
            // Solusi: set schedule sebagai inactive dan tidak schedule notifikasi baru
            // Notifikasi yang sudah scheduled akan tetap deliver (sudah dikirim ke OneSignal)
            
            Log::info('Schedule deactivated from OneSignal', [
                'schedule_id' => $schedule->id,
                'user_id' => $schedule->user_id,
                'medicine' => $schedule->medicine->name ?? 'unknown',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error deactivating OneSignal schedule', [
                'error' => $e->getMessage(),
                'schedule_id' => $schedule->id,
            ]);
            return false;
        }
    }

    /**
     * Schedule satu notifikasi di OneSignal
     */
    private function scheduleNotification(
        User $user,
        MedicationSchedule $schedule,
        Carbon $notificationTimeUtc,
        string $reminderType = 'first',
        Carbon $notificationTimeLocal = null
    ): bool {
        $medicineDose = $schedule->medicine->dose . ' ' . ($schedule->medicine->unit ?? '');
        $sendAfter = $notificationTimeUtc->format('Y-m-d\TH:i:s\Z');

        // Tentukan heading dan content berdasarkan tipe reminder
        if ($reminderType === 'first') {
            $heading = '💊 Waktu Minum Obat';
            $content = "{$schedule->medicine->name} ({$medicineDose}) - Jadwal: {$schedule->time}";
        } else {
            $heading = '⏰ Pengingat Kedua';
            $content = "Sudah minum {$schedule->medicine->name}? Jadwal: {$schedule->time}";
        }

        $payload = [
            'app_id' => config('services.onesignal.app_id'),
            'included_segments' => ['All'],
            'target_channel' => 'push',
            'headings' => ['en' => $heading],
            'contents' => ['en' => $content],
            'url' => url('/app/dashboard'),
            'send_after' => $sendAfter,
            'data' => [
                'schedule_id' => $schedule->id,
                'medicine_id' => $schedule->medicine_id,
                'reminder_type' => $reminderType,
                'user_email' => $user->email,
            ]
        ];

        try {
            // Log payload untuk debugging
            Log::debug('Sending OneSignal notification', [
                'user_email' => $user->email,
                'medicine' => $schedule->medicine->name,
                'reminder_type' => $reminderType,
                'send_after' => $sendAfter,
                'payload' => $payload,
            ]);

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => config('services.onesignal.rest_api_key'),
                'Content-Type' => 'application/json; charset=utf-8',
            ])->post(self::ONESIGNAL_API_URL, $payload);

            $responseData = $response->json();

            // Debug: Log full response
            Log::debug('OneSignal API response', [
                'status_code' => $response->status(),
                'response' => $responseData,
            ]);

            // Check jika API mengembalikan error
            if ($response->failed()) {
                Log::warning('OneSignal API HTTP error', [
                    'status' => $response->status(),
                    'user_email' => $user->email,
                    'medicine' => $schedule->medicine->name,
                    'reminder_type' => $reminderType,
                    'scheduled_time_utc' => $sendAfter,
                    'response' => $responseData,
                ]);
                return false;
            }

            // Check jika response body mengandung error (even if HTTP 200)
            if (isset($responseData['errors']) && !empty($responseData['errors'])) {
                Log::warning('OneSignal response contains errors', [
                    'user_email' => $user->email,
                    'medicine' => $schedule->medicine->name,
                    'reminder_type' => $reminderType,
                    'errors' => $responseData['errors'],
                    'payload' => $payload,
                ]);
                return false;
            }

            // Verify notification id ada di response
            if (!isset($responseData['id']) || empty($responseData['id'])) {
                Log::warning('OneSignal response missing notification id', [
                    'user_email' => $user->email,
                    'medicine' => $schedule->medicine->name,
                    'response' => $responseData,
                    'payload' => $payload,
                ]);
                return false;
            }

            $localDisplay = $notificationTimeLocal ? $notificationTimeLocal->format('Y-m-d H:i') : 'N/A';

            Log::info('Notification scheduled successfully on OneSignal', [
                'notification_id' => $responseData['id'],
                'user_email' => $user->email,
                'medicine' => $schedule->medicine->name,
                'reminder_type' => $reminderType,
                'local_time' => $localDisplay,
                'utc_time' => $sendAfter,
                'schedule_id' => $schedule->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error scheduling notification on OneSignal', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'schedule_id' => $schedule->id,
                'reminder_type' => $reminderType,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
