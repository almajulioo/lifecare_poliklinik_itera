<?php

namespace App\Notifications;

use App\Notifications\Channels\KreaitFcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class MedicationReminderNotification extends Notification
{
    use Queueable;

    protected string $medicineName;
    protected string $medicineDose;
    protected string $scheduledTime;
    protected int $medicationScheduleId;
    protected string $reminderType; // 'first' atau 'second'

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $medicineName,
        string $medicineDose,
        string $scheduledTime,
        int $medicationScheduleId,
        string $reminderType = 'first'
    ) {
        $this->medicineName = $medicineName;
        $this->medicineDose = $medicineDose;
        $this->scheduledTime = $scheduledTime;
        $this->medicationScheduleId = $medicationScheduleId;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [KreaitFcmChannel::class];
    }

    /**
     * Build the title dan body berdasarkan reminder type
     */
    protected function getTitleAndBody(): array
    {
        if ($this->reminderType === 'second') {
            return [
                'title' => '⏰ Pengingat Kedua - Minum Obat',
                'body' => "Apakah Anda sudah minum obat {$this->medicineName}? Jadwal: {$this->scheduledTime}",
            ];
        }

        // Default untuk first reminder
        return [
            'title' => '💊 Waktu Minum Obat',
            'body' => "{$this->medicineName} ({$this->medicineDose}) - Jadwal: {$this->scheduledTime}",
        ];
    }

    /**
     * Send via Kreait SDK directly
     */
    public function sendViaKreait($notifiable)
    {
        try {
            $factory = new \Kreait\Firebase\Factory();
            $factory = $factory->withServiceAccount(storage_path('app/firebase_credentials.json'));
            $messaging = $factory->createMessaging();

            $titleAndBody = $this->getTitleAndBody();

            $notification = \Kreait\Firebase\Messaging\Notification::create(
                $titleAndBody['title'],
                $titleAndBody['body']
            );

            $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification($notification)
                ->withData([
                    'title' => $titleAndBody['title'],
                    'body' => $titleAndBody['body'],
                    'medicine_name' => $this->medicineName,
                    'medicine_dose' => $this->medicineDose,
                    'scheduled_time' => $this->scheduledTime,
                    'medication_schedule_id' => (string)$this->medicationScheduleId,
                    'reminder_type' => $this->reminderType,
                    'route' => '/app/dashboard',
                    'action' => 'medication_reminder',
                ]);

            // Get FCM token from notifiable
            $token = $notifiable->fcm_token ?? null;
            if (!$token) {
                Log::warning('No FCM token for user', ['user_id' => $notifiable->id ?? 'unknown']);
                return false;
            }

            $message = $message->withToken($token);
            $messaging->send($message);
            
            Log::info('FCM medication reminder sent via Kreait', [
                'user_id' => $notifiable->id ?? 'unknown',
                'medication_schedule_id' => $this->medicationScheduleId,
                'reminder_type' => $this->reminderType,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send FCM medication reminder via Kreait', [
                'user_id' => $notifiable->id ?? 'unknown',
                'medication_schedule_id' => $this->medicationScheduleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'medicine_name' => $this->medicineName,
            'medicine_dose' => $this->medicineDose,
            'scheduled_time' => $this->scheduledTime,
            'reminder_type' => $this->reminderType,
        ];
    }
}
