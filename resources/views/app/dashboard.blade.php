@extends('layouts.app_mobile')

@section('content')

<div class="pb-28 bg-white min-h-screen">
    {{-- HEADER SECTION --}}
    <div class="bg-white px-4 pt-4 pb-3 border-b border-gray-100">
        {{-- Greeting + Avatar --}}
        <div class="flex justify-between items-start mb-4">
            <div>
                <div class="text-sm text-gray-500">
                    @php
                        $userTimezone = auth()->user()->timezone ?? 'Asia/Jakarta';
                        $userTime = \Carbon\Carbon::now($userTimezone);
                        $userTime->setLocale('id');
                        $hour = (int)$userTime->format('H');
                        $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 17 ? 'Selamat Siang' : 'Selamat Malam');
                    @endphp
                    {{ $greeting }},
                </div>
                <div class="text-xl font-bold text-gray-900">{{ auth()->user()->name }}</div>
                <div class="text-xs text-gray-400 mt-1">
                    {{ $userTime->translatedFormat('l, j F Y') }}
                </div>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>
    </div>

    <div class="px-4 pt-4 space-y-4">


    {{-- OBAT HARI INI SECTION --}}
    <div class="bg-white rounded-2xl p-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-900">Obat Hari Ini</h2>
            <div class="text-right">
                <div class="text-sm font-semibold text-gray-700">{{ $takenToday }} dari {{ $totalToday }}</div>
                <div class="text-xs text-gray-500">diminum</div>
            </div>
        </div>

        @forelse ($schedules as $schedule)
            @php
                $log = $schedule->logs->first();
                $status = $log?->status ?? 'pending';
                $isTaken = $status === 'taken';
                
                // Handle old JSON format and new H:i format
                $timeStr = $schedule->time;
                if (str_starts_with($timeStr, '[')) {
                    // Old format: ["16:00","04:00"] - take first time
                    $times = json_decode($timeStr, true);
                    $timeStr = is_array($times) ? $times[0] : '00:00';
                }
                $formattedTime = \Carbon\Carbon::createFromFormat('H:i', $timeStr)->format('H:i');
            @endphp

            <div class="flex items-center justify-between p-3 mb-3 rounded-lg border {{ $isTaken ? 'border-green-200 bg-green-50' : 'border-blue-200 bg-blue-50' }}">
                {{-- Left Section: Name & Dose --}}
                <div class="flex-1">
                    <div class="font-semibold text-sm text-gray-900">
                        {{ $schedule->medicine->name }}
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        {{ $schedule->medicine->dose }} {{ $schedule->medicine->unit ?? '' }} · 
                        {{ $formattedTime }}
                    </div>
                </div>

                {{-- Right Section: Status Badge & Action --}}
                <div class="ml-3 text-right">
                    @if($isTaken)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-200 text-green-800">
                            ✓ Diminum
                        </span>
                    @else
                        <form method="POST" action="{{ route('app.schedules.take', $schedule->id) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-200 text-blue-800 hover:bg-blue-300 transition">
                                ◌ Akan Diminum
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-6 text-gray-500">
                <div class="text-2xl mb-2">✨</div>
                <p class="text-sm">Tidak ada jadwal obat untuk hari ini</p>
            </div>
        @endforelse

        {{-- View All Link --}}
        @if($schedules->count() > 0)
        <a href="{{ route('app.medications.index') }}" class="text-blue-600 text-sm font-medium mt-3 block text-center hover:text-blue-700">
            Lihat Semua Obat →
        </a>
        @endif
    </div>

    {{-- SEMUA FITUR SECTION --}}
    <div>
        <h2 class="text-lg font-bold text-gray-900 mb-3 px-1">Semua Fitur</h2>
        
        <div class="grid grid-cols-2 gap-3">
            {{-- Tambah Jadwal Pengingat --}}
            <a href="{{ route('app.schedules.create') }}" class="bg-white rounded-2xl p-6 flex flex-col items-center justify-center border border-gray-100 hover:border-green-300 hover:shadow-md transition">
                <svg class="w-8 h-8 mb-2 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a1 1 0 011 1v8h8a1 1 0 110 2h-8v8a1 1 0 11-2 0v-8H3a1 1 0 110-2h8V3a1 1 0 011-1z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700 text-center">Tambah Jadwal<br>Pengingat</span>
            </a>

            {{-- Buat Jadwal Minum Obat --}}
            <a href="{{ route('app.schedules.upcoming') }}" class="bg-white rounded-2xl p-6 flex flex-col items-center justify-center border border-gray-100 hover:border-purple-300 hover:shadow-md transition">
                <svg class="w-8 h-8 mb-2 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M5 3a2 2 0 012-2h10a2 2 0 012 2v2h4a2 2 0 012 2v13a2 2 0 01-2 2H2a2 2 0 01-2-2V7a2 2 0 012-2h4V3zM7 5h10V3H7v2z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700 text-center">Jadwal<br>Minum Obat</span>
            </a>
        </div>
    </div>

</div>

{{-- Mobile Bottom Navigation --}}
<x-mobile-bottom-nav active="dashboard" />

@endsection