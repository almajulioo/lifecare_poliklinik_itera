<?php

namespace App\Notifications;

use App\Notifications\Channels\KreaitFcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class FcmTestNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $body;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title = 'Test Notification', string $body = 'This is a test notification from Laravel FCM.')
    {
        $this->title = $title;
        $this->body = $body;
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
     * Send via Kreait SDK directly
     */
    public function sendViaKreait($notifiable)
    {
        try {
            $factory = new \Kreait\Firebase\Factory();
            $factory = $factory->withServiceAccount(storage_path('app/firebase_credentials.json'));
            $messaging = $factory->createMessaging();

            $notification = \Kreait\Firebase\Messaging\Notification::create(
                $this->title,
                $this->body
            );

            $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification($notification)
                ->withData([
                    'title' => $this->title,
                    'body' => $this->body,
                    'route' => '/app/dashboard',
                    'status' => 'done',
                ]);

            // Get FCM token from notifiable
            $token = $notifiable->fcm_token ?? null;
            if (!$token) {
                Log::warning('No FCM token for user', ['user_id' => $notifiable->id ?? 'unknown']);
                return false;
            }

            $message = $message->withToken($token);
            $messaging->send($message);
            
            Log::info('FCM test notification sent via Kreait', [
                'user_id' => $notifiable->id ?? 'unknown',
                'title' => $this->title,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send FCM test notification via Kreait', [
                'user_id' => $notifiable->id ?? 'unknown',
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
            'title' => $this->title,
            'body' => $this->body,
        ];
    }
}
