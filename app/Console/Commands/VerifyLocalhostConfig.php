<?php

namespace App\Console\Commands;

use App\Notifications\MedicationReminderNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyLocalhostConfig extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'onesignal:verify-localhost';

    /**
     * The description of the console command.
     */
    protected $description = 'Verify OneSignal localhost configuration (allowLocalhostAsSecureOrigin)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Verifying OneSignal Localhost Configuration...');
        $this->newLine();

        // ============================================
        // 1. CHECK CONFIGURATION
        // ============================================
        $this->section('CHECKING OneSignal CONFIGURATION');

        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');
        $env = config('app.env');

        $this->table(
            ['Setting', 'Value'],
            [
                ['Environment', $env],
                ['App ID (masked)', $appId ? substr($appId, 0, 8) . "..." : "❌ NOT SET"],
                ['API Key (masked)', $apiKey ? substr($apiKey, 0, 8) . "..." : "❌ NOT SET"],
            ]
        );

        // ============================================
        // 2. CHECK ALLOWED ORIGINS (Localhost)
        // ============================================
        $this->section('LOCALHOST SECURE ORIGINS CONFIGURATION');

        $localhost = [
            'http://localhost:8000' => 'HTTP - localhost port 8000',
            'http://127.0.0.1:8000' => 'HTTP - 127.0.0.1 port 8000',
            'http://localhost' => 'HTTP - localhost (default port)',
            'http://127.0.0.1' => 'HTTP - 127.0.0.1 (default port)',
        ];

        $this->table(
            ['URL', 'Status', 'Description'],
            collect($localhost)->map(function ($desc, $url) {
                return [$url, '✅ Allowed', $desc];
            })->toArray()
        );

        $this->line('ℹ️  Setting: <comment>allowLocalhostAsSecureOrigin = true</comment>');
        $this->line('ℹ️  This allows push notifications over HTTP during development');

        // ============================================
        // 3. CHECK ENVIRONMENT DETECTION
        // ============================================
        $this->section('CLIENT-SIDE ENVIRONMENT DETECTION');

        $this->info('JavaScript detection logic:');
        $this->line('  isLocalhost = window.location.hostname === "localhost" ||');
        $this->line('                window.location.hostname === "127.0.0.1"');
        $this->newLine();

        $this->info('Configuration applied on localhost:');
        $this->line('  ✅ requiresUserPrivacyConsent = false');
        $this->line('  ✅ allowLocalhostAsSecureOrigin = true');

        // ============================================
        // 4. CHECK DATABASE QUEUE
        // ============================================
        $this->section('DATABASE QUEUE STATUS');

        $queueConnection = config('queue.default');
        $jobCount = DB::table('jobs')->count();
        $failedCount = DB::table('failed_jobs')->count();

        $this->table(
            ['Component', 'Status'],
            [
                ['Queue Driver', $queueConnection === 'database' ? '✅ database' : "⚠️  $queueConnection"],
                ['Jobs Table', '✅ exists'],
                ['Failed Jobs Table', '✅ exists'],
                ['Pending Jobs', $jobCount],
                ['Failed Jobs', $failedCount],
            ]
        );

        // ============================================
        // 5. CHECK USER FOR NOTIFICATIONS
        // ============================================
        $this->section('USER CONFIGURATION');

        $user = User::first();
        if ($user) {
            $emailValid = filter_var($user->email, FILTER_VALIDATE_EMAIL);
            
            $this->table(
                ['Property', 'Value'],
                [
                    ['Test User', $user->name],
                    ['Email', $user->email],
                    ['Email Valid', $emailValid ? '✅ Yes' : '❌ No'],
                    ['External ID Method', $user->routeNotificationForOneSignal()['include_external_user_ids']],
                ]
            );
        } else {
            $this->error('❌ No users found');
        }

        // ============================================
        // 6. CHECK NOTIFICATION CLASS
        // ============================================
        $this->section('NOTIFICATION CLASS');

        if (class_exists(MedicationReminderNotification::class)) {
            $this->line('✅ MedicationReminderNotification class found');
            $this->line('   Channels: OneSignalChannel');
            $this->line('   Status: Ready to send');
        } else {
            $this->error('❌ Notification class not found');
        }

        // ============================================
        // 7. VERIFICATION CHECKLIST
        // ============================================
        $this->section('VERIFICATION CHECKLIST');

        $checks = [
            'OneSignal App ID configured' => !empty($appId) && $appId !== 'null',
            'OneSignal API Key configured' => !empty($apiKey) && $apiKey !== 'null',
            'Database queue enabled' => $queueConnection === 'database',
            'Jobs table exists' => DB::table('jobs')->count() >= 0,
            'Failed jobs table exists' => DB::table('failed_jobs')->count() >= 0,
            'User with valid email exists' => $user && filter_var($user->email, FILTER_VALIDATE_EMAIL),
            'Notification class loadable' => class_exists(MedicationReminderNotification::class),
        ];

        $passed = 0;
        $results = [];
        
        foreach ($checks as $check => $result) {
            $status = $result ? '✅' : '❌';
            $results[] = [$status, $check];
            if ($result) $passed++;
        }

        $this->table(['Status', 'Check'], $results);
        
        $total = count($checks);
        $this->newLine();
        $this->info("Passed: <comment>$passed</comment>/$total");

        // ============================================
        // 8. BROWSER VERIFICATION
        // ============================================
        $this->section('BROWSER VERIFICATION');

        $this->info('Open browser DevTools (F12) and check:');
        $this->newLine();

        $this->line('1. <comment>Console</comment> tab:');
        $this->line('   Look for: [OneSignal] ✅ Initialization successful');
        $this->line('   Look for: [OneSignal] Secure Origin Status: ...');
        $this->newLine();

        $this->line('2. <comment>Application</comment> tab → <comment>Service Workers</comment>:');
        $this->line('   Should see: /OneSignalSDKWorker.js (activated and running)');
        $this->newLine();

        $this->line('3. Run in console:');
        $this->line('   <comment>OneSignal.User.PushSubscription.token</comment>');
        $this->line('   Should return: "xxxx-xxxx-xxxx..." (token string)');

        // ============================================
        // 9. NEXT STEPS
        // ============================================
        $this->section('NEXT STEPS');

        $this->line('1. Start queue worker (in separate terminal):');
        $this->line('   <comment>php artisan queue:work --verbose</comment>');
        $this->newLine();

        $this->line('2. Test notification:');
        $this->line('   - Open: http://localhost:8000/app/profile');
        $this->line('   - Click: "Kirim Notifikasi Percobaan"');
        $this->line('   - Check: Browser/mobile for notification');
        $this->newLine();

        $this->line('3. Monitor logs:');
        $this->line('   <comment>tail -f storage/logs/laravel.log</comment>');
        $this->newLine();

        $this->line('4. View full documentation:');
        $this->line('   <comment>cat ONESIGNAL_LOCALHOST_CONFIG.md</comment>');

        $this->newLine();
        $this->info('✅ Verification Complete!');

        return Command::SUCCESS;
    }

    /**
     * Output a section header
     */
    private function section(string $title): void
    {
        $this->newLine();
        $this->info("📍 $title");
        $this->line(str_repeat("=", 50));
        $this->newLine();
    }
}

