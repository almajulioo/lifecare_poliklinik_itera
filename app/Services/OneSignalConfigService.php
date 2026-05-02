<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * Service untuk memvalidasi dan mengelola konfigurasi OneSignal
 */
class OneSignalConfigService
{
    /**
     * Check apakah OneSignal sudah dikonfigurasi dengan benar
     */
    public static function isConfigured(): bool
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');
        
        return !empty($appId) && !empty($apiKey) && 
               $appId !== 'null' && $apiKey !== 'null';
    }

    /**
     * Get validasi status OneSignal
     */
    public static function getConfigurationStatus(): array
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');
        $restUrl = config('services.onesignal.rest_api_url');

        return [
            'app_id_configured' => !empty($appId) && $appId !== 'null',
            'api_key_configured' => !empty($apiKey) && $apiKey !== 'null',
            'api_url_configured' => !empty($restUrl),
            'is_fully_configured' => self::isConfigured(),
            'app_id' => $appId ? substr($appId, 0, 8) . '...' : 'NOT SET',
            'api_key' => $apiKey ? substr($apiKey, 0, 8) . '...' : 'NOT SET',
            'api_url' => $restUrl,
            'environment' => config('app.env'),
        ];
    }

    /**
     * Validate user untuk notifikasi OneSignal
     */
    public static function validateUserForNotification($user): array
    {
        $errors = [];

        if (!$user) {
            $errors[] = 'User tidak ditemukan';
            return ['valid' => false, 'errors' => $errors];
        }

        if (!$user->email) {
            $errors[] = 'User email tidak ditemukan - diperlukan untuk OneSignal targeting';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'user_id' => $user->id,
            'user_email' => $user->email ?? null,
        ];
    }

    /**
     * Log configuration check result
     */
    public static function logConfigurationCheck(): void
    {
        $status = self::getConfigurationStatus();
        
        if (!self::isConfigured()) {
            Log::warning('[OneSignal] Configuration incomplete', $status);
        } else {
            Log::info('[OneSignal] Configuration verified', $status);
        }
    }

    /**
     * Get diagnostic information untuk troubleshooting
     */
    public static function getDiagnostics(): array
    {
        return [
            'configuration' => self::getConfigurationStatus(),
            'queue' => [
                'driver' => config('queue.default'),
                'connection' => config('queue.connections.' . config('queue.default')),
            ],
            'notification' => [
                'channels' => config('app.notification_channels', ['single']),
                'from' => config('mail.from'),
            ],
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
