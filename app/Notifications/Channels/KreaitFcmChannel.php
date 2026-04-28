<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class KreaitFcmChannel
{
    public function send($notifiable, Notification $notification)
    {
        // Call the notification's custom send method if it exists
        if (method_exists($notification, 'sendViaKreait')) {
            return $notification->sendViaKreait($notifiable);
        }

        return false;
    }
}
