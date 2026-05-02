<?php

namespace App\Console\Commands;

use App\Services\OneSignalConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOneSignalConfig extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'onesignal:check-config';

    /**
     * The description of the console command.
     */
    protected $description = 'Check OneSignal configuration and send test notification';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking OneSignal Configuration...');
        $this->newLine();

        // Get configuration status
        $status = OneSignalConfigService::getConfigurationStatus();

        $this->table(
            ['Setting', 'Status'],
            [
                ['App ID', $status['app_id_configured'] ? '✅ Configured' : '❌ MISSING'],
                ['API Key', $status['api_key_configured'] ? '✅ Configured' : '❌ MISSING'],
                ['API URL', $status['api_url_configured'] ? '✅ Configured' : '❌ MISSING'],
                ['Overall', $status['is_fully_configured'] ? '✅ READY' : '❌ INCOMPLETE'],
            ]
        );

        $this->newLine();
        $this->info('📋 Configuration Details:');
        $this->table(
            ['Key', 'Value'],
            [
                ['App ID (masked)', $status['app_id']],
                ['API Key (masked)', $status['api_key']],
                ['API URL', $status['api_url']],
                ['Environment', $status['environment']],
            ]
        );

        $this->newLine();

        if (!$status['is_fully_configured']) {
            $this->error('❌ OneSignal is NOT properly configured!');
            $this->info('Please set the following environment variables in .env:');
            $this->line('  ONESIGNAL_APP_ID=<your_app_id>');
            $this->line('  ONESIGNAL_REST_API_KEY=<your_api_key>');
            return Command::FAILURE;
        }

        $this->info('✅ OneSignal configuration is valid!');

        // Check diagnostics
        $diagnostics = OneSignalConfigService::getDiagnostics();
        
        $this->info('📊 Queue Configuration:');
        $this->line('  Driver: ' . $diagnostics['queue']['driver']);

        if ($diagnostics['queue']['driver'] === 'database') {
            $this->line('  ✅ Database queue is configured');
        } else if ($diagnostics['queue']['driver'] === 'sync') {
            $this->warn('  ⚠️  Sync queue - notifications will be sent immediately (consider using database queue for production)');
        }

        $this->newLine();
        $this->info('✅ OneSignal setup appears to be correct!');
        $this->info('Run: php artisan queue:work (if using database queue)');
        $this->info('Or test with: php artisan tinker');
        $this->info('  $user = App\\Models\\User::first();');
        $this->info('  \\Notification::send($user, new App\\Notifications\\MedicationReminderNotification(...));');

        return Command::SUCCESS;
    }
}
