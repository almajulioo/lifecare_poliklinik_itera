@extends('layouts.app_mobile')

@section('content')

<div class="pb-28">
    {{-- Header --}}
    <div class="bg-white rounded-xl p-4 shadow-sm border mb-4">
        <h1 class="text-lg font-semibold">⚙️ Pengaturan Notifikasi</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola notifikasi pengingat minum obat</p>
    </div>

    {{-- Settings Form --}}
    <form id="notificationSettingsForm" class="space-y-4">
        @csrf

        {{-- Enable/Disable Toggle --}}
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold">Aktifkan Notifikasi</h3>
                    <p class="text-xs text-gray-500 mt-1">Nyalakan untuk menerima pengingat minum obat</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="enabled" name="enabled" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        {{-- Sound Toggle --}}
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold">Suara Notifikasi</h3>
                    <p class="text-xs text-gray-500 mt-1">Mainkan suara saat notifikasi muncul</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="sound_enabled" name="sound_enabled" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        {{-- Vibration Toggle --}}
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold">Getaran</h3>
                    <p class="text-xs text-gray-500 mt-1">Getar perangkat saat notifikasi</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="vibration_enabled" name="vibration_enabled" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        {{-- Advance Minutes --}}
        <div class="bg-white rounded-xl p-4 shadow-sm border">
            <label class="block">
                <h3 class="font-semibold mb-2">Pengingat Sebelum</h3>
                <p class="text-xs text-gray-500 mb-3">Terima notifikasi berapa menit sebelum jadwal?</p>
                
                <select id="advance_minutes" name="advance_minutes" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="0">On time (tepat waktu)</option>
                    <option value="5">5 menit sebelumnya</option>
                    <option value="10">10 menit sebelumnya</option>
                    <option value="15">15 menit sebelumnya</option>
                    <option value="30">30 menit sebelumnya</option>
                </select>
            </label>
        </div>

        {{-- Do Not Disturb Section --}}
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
            <h3 class="font-semibold text-blue-900 mb-3">🌙 Jangan Ganggu (Do Not Disturb)</h3>
            <p class="text-xs text-blue-700 mb-4">Notifikasi tidak akan dikirim di periode ini</p>

            <div class="space-y-3">
                {{-- DND Start Time --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Mulai dari
                    </label>
                    <input type="time" id="dnd_start" name="dnd_start" value="22:00"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- DND End Time --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Sampai
                    </label>
                    <input type="time" id="dnd_end" name="dnd_end" value="08:00"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="text-xs text-blue-700 mt-2 p-2 bg-blue-100 rounded">
                    ℹ️ Contoh: 22:00 - 08:00 = notifikasi dimatikan pukul 10 malam sampai 8 pagi
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <button type="submit" 
            class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition">
            💾 Simpan Pengaturan
        </button>

        {{-- Success Message --}}
        <div id="successMessage" style="display: none;" 
            class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-3 text-sm">
            ✓ Pengaturan berhasil disimpan
        </div>

        {{-- Error Message --}}
        <div id="errorMessage" style="display: none;"
            class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-3 text-sm">
            ✗ Terjadi kesalahan. Silakan coba lagi.
        </div>
    </form>

    {{-- Test Notification Button --}}
    <div class="bg-white rounded-xl p-4 shadow-sm border mt-4">
        <button id="testNotificationBtn" 
            class="w-full bg-amber-500 text-white py-2 rounded-lg font-medium hover:bg-amber-600 transition">
            🔔 Tes Notifikasi
        </button>
        <p class="text-xs text-gray-500 text-center mt-2">Klik untuk melihat notifikasi contoh</p>
    </div>

</div>

<x-mobile-bottom-nav active="profile" />

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('notificationSettingsForm');
    const successMsg = document.getElementById('successMessage');
    const errorMsg = document.getElementById('errorMessage');
    const testBtn = document.getElementById('testNotificationBtn');

    /**
     * Load preferences from API and populate form
     */
    async function loadPreferences() {
        try {
            const response = await fetch('/api/notification-preferences');
            if (!response.ok) throw new Error('Failed to load preferences');

            const data = await response.json();
            const prefs = data.preferences;

            // Populate form
            document.getElementById('enabled').checked = prefs.enabled;
            document.getElementById('sound_enabled').checked = prefs.sound_enabled;
            document.getElementById('vibration_enabled').checked = prefs.vibration_enabled;
            document.getElementById('advance_minutes').value = prefs.advance_minutes;
            document.getElementById('dnd_start').value = prefs.dnd_start;
            document.getElementById('dnd_end').value = prefs.dnd_end;
        } catch (error) {
            console.error('[Settings] Error loading preferences:', error);
        }
    }

    /**
     * Save preferences
     */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        try {
            const formData = new FormData(form);
            const data = {
                enabled: formData.get('enabled') === 'on',
                sound_enabled: formData.get('sound_enabled') === 'on',
                vibration_enabled: formData.get('vibration_enabled') === 'on',
                advance_minutes: parseInt(formData.get('advance_minutes')),
                dnd_start: formData.get('dnd_start'),
                dnd_end: formData.get('dnd_end'),
            };

            const response = await fetch('/api/notification-preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) throw new Error('Failed to save preferences');

            // Show success message
            successMsg.style.display = 'block';
            errorMsg.style.display = 'none';
            
            setTimeout(() => {
                successMsg.style.display = 'none';
            }, 3000);

            // Update scheduler if available
            if (window.notificationScheduler) {
                await window.notificationScheduler.loadPreferences();
            }
        } catch (error) {
            console.error('[Settings] Error saving preferences:', error);
            errorMsg.style.display = 'block';
            successMsg.style.display = 'none';
        }
    });

    /**
     * Test notification
     */
    testBtn.addEventListener('click', async () => {
        try {
            if (window.notificationManager) {
                await window.notificationManager.show({
                    title: '💊 Test Notifikasi',
                    body: 'Ini adalah test notifikasi. Jika Anda melihat pesan ini, notifikasi berhasil!',
                    icon: '💊',
                    badge: '💊',
                    tag: 'test-notification',
                });
            }
        } catch (error) {
            console.error('[Settings] Error testing notification:', error);
        }
    });

    // Load preferences on page load
    loadPreferences();
});
</script>

@endsection
