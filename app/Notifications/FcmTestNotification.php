<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

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
        return [FcmChannel::class];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
                title: $this->title,
                body: $this->body
            )))
            ->data([
                'title' => $this->title,
                'body' => $this->body,
                'route' => '/app/dashboard',
                'status' => 'done',
            ])
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#0A0A0A',
                        'sound' => 'default',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default'
                        ],
                    ],
                ],
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high',
                    ],
                    'notification' => [
                        'title' => $this->title,
                        'body' => $this->body,
                        'icon' => '/favicon.ico',
                    ],
                    'fcm_options' => [
                        'link' => url('/app/dashboard'),
                    ],
                ],
            ]);
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
