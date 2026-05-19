@extends('layouts.app_mobile')

@section('title', 'Jadwal Obat Saya')
@section('header', 'Jadwal Obat Saya')

@section('content')
<div class="space-y-4">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm">
            <strong>✓</strong> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
            <strong>✗</strong> {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-sm">
            <strong>⚠</strong> {{ session('warning') }}
        </div>
    @endif

    <!-- OneSignal Notification Status -->
    <div id="onesignal-status-banner" class="hidden p-4 bg-indigo-50 border border-indigo-200 rounded-xl shadow-sm">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-indigo-900">Aktifkan Notifikasi Push</p>
                <p class="text-xs text-indigo-700 mt-0.5">Dapatkan pengingat minum obat tepat waktu di HP Anda.</p>
            </div>
            <button onclick="OneSignal.Slidedown.promptPush()" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                Aktifkan
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            OneSignalDeferred.push(function(OneSignal) {
                // Sembunyikan banner jika user sudah subscribe
                OneSignal.User.PushSubscription.addEventListener("change", function(event) {
                    if (event.current.token) {
                        document.getElementById('onesignal-status-banner').classList.add('hidden');
                    }
                });

                if (!OneSignal.User.PushSubscription.token) {
                    document.getElementById('onesignal-status-banner').classList.remove('hidden');
                }
            });
        });
    </script>

    <!-- Statistics -->
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-xs font-medium text-blue-600">Total</p>
            <p class="text-2xl font-bold text-blue-900 mt-1">{{ $schedules->count() }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-xs font-medium text-green-600">Aktif</p>
            <p class="text-2xl font-bold text-green-900 mt-1">
                {{ $schedules->where('is_active', true)->count() }}
            </p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <p class="text-xs font-medium text-orange-600">Nonaktif</p>
            <p class="text-2xl font-bold text-orange-900 mt-1">
                {{ $schedules->where('is_active', false)->count() }}
            </p>
        </div>
    </div>

    <!-- Schedules List -->
    @if($schedules->count() > 0)
        <div class="space-y-2">
            @foreach($schedules as $schedule)
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-gray-900">{{ $schedule->medicine->name }}</p>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $schedule->source_type == 'ADMIN' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $schedule->source_type == 'ADMIN' ? 'Dari Admin' : 'Pribadi' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $schedule->medicine->dose }} {{ $schedule->medicine->unit }}</p>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $schedule->is_active ? '✓' : '✗' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3 text-xs text-gray-600">
                        <div>
                            <p class="text-gray-500">Jam</p>
                            <p class="font-medium">
                                @php
                                    $timeDisplay = $schedule->time;
                                    if (strpos($timeDisplay, ':') != false) {
                                        $timeDisplay = substr($timeDisplay, 0, 5);
                                    }
                                @endphp
                                {{ $timeDisplay }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500">Periode</p>
                            <p class="font-medium">{{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2 pt-2 border-t">
                        <a href="{{ route('app.schedules.edit', $schedule) }}" class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 rounded text-center text-xs font-medium hover:bg-blue-200 transition">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('app.schedules.destroy', $schedule) }}" style="flex: 1;" onsubmit="return confirm('Hapus jadwal?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-3 py-2 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200 transition">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
        
        
            @endforeach
        </div>

        <!-- Button Buat Jadwal -->
        <a href="{{ route('app.schedules.create') }}" class="block px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition text-center text-sm">
            + Jadwal Baru
        </a>

    @else
        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
            <p class="text-gray-500 mb-3">Belum ada jadwal obat</p>
            <a href="{{ route('app.schedules.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                Buat jadwal pertama →
            </a>
        </div>
    


    @endif
</div>
@endsection
