<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class MedicationReminderNotification extends Notification
{
    use Queueable;

    protected string $medicineName;
    protected string $medicineDose;
    protected string $scheduledTime;
    protected int $medicationScheduleId;
    protected string $reminderType; // 'first' atau 'second'
    protected ?string $sendAfter;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $medicineName,
        string $medicineDose,
        string $scheduledTime,
        int $medicationScheduleId,
        string $reminderType = 'first',
        ?string $sendAfter = null
    ) {
        $this->medicineName = $medicineName;
        $this->medicineDose = $medicineDose;
        $this->scheduledTime = $scheduledTime;
        $this->medicationScheduleId = $medicationScheduleId;
        $this->reminderType = $reminderType;
        $this->sendAfter = $sendAfter;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [OneSignalChannel::class];
    }

    /**
     * Build OneSignal notification message.
     */
    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $titleAndBody = $this->getTitleAndBody();
        $message = OneSignalMessage::create()
            ->setSubject($titleAndBody['title'])
            ->setBody($titleAndBody['body'])
            ->setUrl(url('/app/dashboard'));


        if ($this->sendAfter) {
            try {
                $sendTime = \Carbon\Carbon::parse($this->sendAfter, 'Asia/Jakarta');
                // Gunakan format ISO 8601 atau format UTC yang sangat standar
                $formattedTime = $sendTime->utc()->format('Y-m-d H:i:s \U\T\C');
                $message->setParameter('send_after', $formattedTime);
                
                // TAMBAHKAN INI: Memastikan OneSignal tidak menggunakan segmen default
                $message->setParameter('isAnyWeb', true); 
            } catch (\Exception $e) {
            }
        }

        return $message;
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

        if ($this->reminderType === 'confirmation') {
            return [
                'title' => '✅ Jadwal Pengingat Aktif',
                'body' => "Jadwal minum obat {$this->medicineName} ({$this->medicineDose}) telah berhasil diatur.",
            ];
        }

        // Default untuk first reminder
        return [
            'title' => '💊 Waktu Minum Obat',
            'body' => "{$this->medicineName} ({$this->medicineDose}) - Jadwal: {$this->scheduledTime}",
        ];
    }

    /**
     * Get the array representation of the notification.
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
