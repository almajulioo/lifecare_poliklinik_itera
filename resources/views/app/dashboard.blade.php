@extends('layouts.app_mobile')

@section('content')

<div class="pb-28 bg-white min-h-screen">
    {{-- HEADER SECTION --}}
    <div class="bg-white px-4 pt-4 pb-4 border-b border-gray-100 sticky top-0 z-10">
        {{-- Greeting + Avatar --}}
        <div class="flex justify-between items-start mb-3">
            <div>
                <div class="text-xs text-gray-500 font-medium mb-1">
                    @php
                        $userTime = \Carbon\Carbon::now();
                        $hour = (int)$userTime->format('H');
                        $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 17 ? 'Selamat Siang' : 'Selamat Malam');
                    @endphp
                    {{ $greeting }}
                </div>
                <div class="text-xl font-bold text-gray-900">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-400 mt-0.5">
                    {{ $userTime->translatedFormat('l, j F Y') }}
                </div>
            </div>
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-base shadow-md">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>
    </div>

    <div class="px-4 pt-4 space-y-4">

    {{-- REMINDER LIST SECTION --}}
    @if($dueMedications->count() > 0)
    <div class="space-y-2">
        @foreach($dueMedications as $medication)
            @php
                $timeStr = $medication->time;
                if (str_starts_with($timeStr, '[')) {
                    $times = json_decode($timeStr, true);
                    $timeStr = is_array($times) ? $times[0] : '00:00';
                }
                $formattedTime = \Carbon\Carbon::createFromFormat('H:i', $timeStr)->format('H:i');
            @endphp
            
            <div class="bg-red-50 border border-red-300 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <div class="font-semibold text-red-900 text-sm">
                            💊 {{ $medication->medicine->name }}
                        </div>
                        <div class="text-red-700 text-xs">
                            {{ $medication->medicine->dose }} {{ $medication->medicine->unit ?? '' }} · Jam {{ $formattedTime }}
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('app.schedules.take', $medication->id) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white text-xs font-bold py-2 px-3 rounded transition">
                            ✓ Sudah Minum
                        </button>
                    </form>
                    <form method="POST" action="{{ route('app.schedules.later', $medication->id) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold py-2 px-3 rounded transition">
                            Tunda 5 Menit
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- OBAT HARI INI SECTION --}}
    <div>
        <h2 class="text-base font-bold text-gray-900 mb-3">Obat Hari Ini</h2>
        
        <div class="space-y-2">
            {{-- Stats Counter --}}
            <div class="text-right mb-3">
                <div class="text-lg font-bold text-gray-900">{{ $takenToday }} dari {{ $totalToday }}</div>
                <div class="text-xs text-gray-500">diminum</div>
            </div>

            @forelse ($schedules as $schedule)
                @php
                    $log = $schedule->logs->first();
                    $status = $log?->status ?? 'pending';
                    $isTaken = $status == 'taken';
                    
                    // Handle old JSON format and new H:i format
                    $timeStr = $schedule->time;
                    if (str_starts_with($timeStr, '[')) {
                        // Old format: ["16:00","04:00"] - take first time
                        $times = json_decode($timeStr, true);
                        $timeStr = is_array($times) ? $times[0] : '00:00';
                    }
                    $formattedTime = \Carbon\Carbon::createFromFormat('H:i', $timeStr)->format('H:i');
                @endphp

                <div class="flex items-center justify-between p-3 rounded-lg {{ $isTaken ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                    {{-- Left Section: Name & Dose --}}
                    <div class="flex-1">
                        <div class="font-semibold text-sm {{ $isTaken ? 'text-green-700' : 'text-gray-700' }}">
                            {{ $schedule->medicine->name }}
                        </div>
                        <div class="text-xs text-gray-600 mt-1">
                            {{ $schedule->medicine->dose }} {{ $schedule->medicine->unit ?? '' }} · {{ $formattedTime }}
                        </div>
                    </div>

                    {{-- Right Section: Status Badge --}}
                    <div class="ml-3 flex-shrink-0">
                        @if($isTaken)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                ✓ Diminum
                            </span>
                        @else
                            <form method="POST" action="{{ route('app.schedules.take', $schedule->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                                    ◉ Minum
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-6 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl mb-2">✨</div>
                    <p class="text-sm text-gray-500">Tidak ada jadwal obat untuk hari ini</p>
                </div>
            @endforelse

            {{-- View All Link --}}
            @if($schedules->count() > 0)
            <a href="{{ route('app.medications.index') }}" class="text-blue-600 text-sm font-semibold mt-3 block text-center hover:text-blue-700">
                Lihat Semua Obat →
            </a>
            @endif
        </div>
    </div>

    {{-- SEMUA FITUR SECTION --}}
    <div>
        <h2 class="text-base font-bold text-gray-900 mb-3">Semua Fitur</h2>
        
        <div class="grid grid-cols-2 gap-4">
            {{-- Tambah Jadwal Pengingat --}}
            <a href="{{ route('app.schedules.create') }}" class="bg-white rounded-lg p-5 flex flex-col items-center justify-center border border-gray-200 hover:border-green-300 hover:bg-green-50 transition duration-200">
                <svg class="w-8 h-8 mb-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-900 text-center">Tambah Jadwal</span>
                <span class="text-xs text-gray-500 text-center mt-1">Pengingat</span>
            </a>

            {{-- Jadwal Minum Obat --}}
            <a href="{{ route('app.schedules.upcoming') }}" class="bg-white rounded-lg p-5 flex flex-col items-center justify-center border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition duration-200">
                <svg class="w-8 h-8 mb-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-900 text-center">Jadwal</span>
                <span class="text-xs text-gray-500 text-center mt-1">Minum Obat</span>
            </a>
        </div>
    </div>

</div>

{{-- Mobile Bottom Navigation --}}
<x-mobile-bottom-nav active="dashboard" />

<script>
// Auto-check untuk due medications setiap 30 detik
let lastMedicationCount = {{ $dueMedications->count() }};
let checkInterval;

function checkDueMedications() {
    fetch('/api/due-medications', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.count !== lastMedicationCount) {
            // Ada perubahan, reload halaman untuk update
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error checking due medications:', error);
    });
}

// Start auto-checking saat page load
document.addEventListener('DOMContentLoaded', function() {
    // Cek setiap 30 detik
    checkInterval = setInterval(checkDueMedications, 30000);
    
    // Juga cek saat page menjadi visible (keluar dari background)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            console.log('Dashboard is visible, checking for due medications...');
            checkDueMedications();
        }
    });
});

// Stop checking saat page unload
window.addEventListener('beforeunload', function() {
    if (checkInterval) {
        clearInterval(checkInterval);
    }
});

function snoozeReminder(btn, medicationScheduleId) {
    // Disable button to prevent multiple clicks
    btn.disabled = true;
    btn.textContent = 'Menyimpan Tunda...';
    
    fetch('/api/snooze-reminder-dashboard', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            medication_schedule_id: medicationScheduleId,
            snooze_minutes: 5
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pengingat ditunda 5 menit, kami akan ingatkan lagi.');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.textContent = 'Tunda 5 Menit';
    });
}
</script>

@endsection