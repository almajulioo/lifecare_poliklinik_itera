<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
    <title>@yield('title', 'LifeCare+')</title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 192 192'%3E%3Crect fill='%23000' width='192' height='192'/%3E%3Ctext x='50%' y='50%' font-size='90' font-weight='bold' fill='white' text-anchor='middle' dy='.3em'%3E💊%3C/text%3E%3C/svg%3E">
    <link rel="apple-touch-icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'%3E%3Crect fill='%23000' width='180' height='180' rx='40'/%3E%3Ctext x='50%' y='50%' font-size='80' font-weight='bold' fill='white' text-anchor='middle' dy='.3em'%3E💊%3C/text%3E%3C/svg%3E">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="LifeCare+">
    <meta name="theme-color" content="#000000">
    <meta name="description" content="Aplikasi pengingat minum obat dan manajemen pasien poliklinik">
    
    <!-- Security Headers -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50" data-offline="false">
    <div class="min-h-screen pb-20">
        <!-- Offline Indicator (will be shown by offline-detector.js) -->
        <div id="offline-indicator" style="display: none;"></div>
        
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white border-b pt-safe">
            <div class="px-4 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="/images/logo-itera.png" alt="Logo ITERA" class="h-10 w-10 object-contain">
                        <div>
                            <div class="text-sm text-gray-500">LifeCare+</div>
                            <div class="font-semibold text-gray-900">
                                @yield('header', 'Poliklinik ITERA')
                            </div>
                        </div>
                    </div>
                    <!-- Sync Indicator -->
                    <div id="offline-queue-indicator" style="display: none;" class="flex items-center gap-2 px-2 py-1 bg-blue-100 rounded-lg text-xs text-blue-700">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Syncing... <strong data-count="0">0</strong></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="px-4 py-4">
            @yield('content')
        </main>
        
        <!-- Bottom Navigation (will be filled by component in Tahap 1) -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 safe-bottom">
            @yield('bottom-nav')
        </nav>
    </div>
    
    <!-- Global Scripts for PWA/Offline -->
    <script>
        // Setup for safe areas (notch devices)
        Object.assign(document.documentElement.style, {
            '--safe-top': 'env(safe-area-inset-top)',
            '--safe-right': 'env(safe-area-inset-right)',
            '--safe-bottom': 'env(safe-area-inset-bottom)',
            '--safe-left': 'env(safe-area-inset-left)',
        });
    </script>

    @auth
        <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
        <script>
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            window.OneSignalConfig = {
                appId: "{{ config('services.onesignal.app_id') }}",
                userEmail: "{{ auth()->user()->email }}",
                userName: "{{ auth()->user()->name }}",
                environment: "{{ config('app.env') }}",
                isLocalhost: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1',
            };

            OneSignalDeferred.push(async function(OneSignal) {
                try {
                    // Initialize OneSignal
                    await OneSignal.init({
                        appId: window.OneSignalConfig.appId,
                        allowLocalhostAsSecureOrigin: true,
                        notifyButton: {
                            enable: true,
                        },
                    });
                    
                    // Login user
                    await OneSignal.login(window.OneSignalConfig.userEmail);
                    
                    // Prompt for push notification permission
                    OneSignal.Slidedown.promptPush();
                    
                    // Handle notification clicks
                    OneSignal.Notifications.addEventListener("click", function(event) {
                        window.location.href = '/app/dashboard';
                    });
                } catch (e) {
                    console.error('[OneSignal] Error:', e.message);
                }
            });
        </script>
    @endauth
</body>
</html>
