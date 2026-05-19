@extends('layouts.app_mobile')

@section('title', 'Jadwal Minum Obat')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white pb-24">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-2xl mx-auto px-4 py-4">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('app.dashboard') }}" class="p-2 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Jadwal Minum Obat</h1>
                </div>
            </div>
            <p class="text-sm text-slate-600 mt-1 ml-11">Jadwal minum obat Anda mendatang</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-4">
        @forelse ($schedulesByDate as $date => $daySchedules)
            <!-- Date Section -->
            <div class="mb-6">
                <!-- Date Header -->
                <div class="mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: var(--primary-color); background-color: #16bac5;">
                            @if ($date == now()->toDateString())
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            @elseif ($date == now()->addDay()->toDateString())
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-9 8h10a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">
                                @if ($date == now()->toDateString())
                                    Hari Ini
                                @elseif ($date == now()->addDay()->toDateString())
                                    Besok
                                @else
                                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                                @endif
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $daySchedules->count() }} jadwal minum obat
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedules for this date -->
                <div class="space-y-3">
                    @foreach ($daySchedules as $schedule)
                        @php
                            // Handle time format
                            $timeStr = $schedule->time;
                            if (str_starts_with($timeStr, '[')) {
                                $times = json_decode($timeStr, true);
                                $timeStr = is_array($times) ? $times[0] : '00:00';
                            }
                            $formattedTime = \Carbon\Carbon::createFromFormat('H:i', $timeStr)->format('H:i');
                        @endphp

                        <div class="bg-white rounded-lg border border-slate-200 p-4 hover:shadow-md transition">
                            <!-- Time & Medicine -->
                            <div class="flex items-start gap-3">
                                <!-- Time -->
                                <div class="bg-blue-100 text-blue-700 rounded-lg px-3 py-2 min-w-max font-semibold text-lg">
                                    {{ $formattedTime }}
                                </div>

                                <!-- Medicine Info -->
                                <div class="flex-1">
                                    <h3 class="font-bold text-slate-900">{{ $schedule->medicine->name }}</h3>
                                    <p class="text-sm text-slate-600 mt-1">
                                        Dosis: {{ $schedule->medicine->dose }} {{ $schedule->medicine->unit }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Jam Minum: {{ $formattedTime }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Frekuensi: {{ $schedule->frequency }}
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Mulai: {{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m/Y') }}
                                        @if($schedule->end_date)
                                            • Selesai: {{ \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') }}
                                        @else
                                            • Selesai: Tidak terbatas
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            @if ($date == now()->toDateString())
                                @php
                                    $log = $schedule->logs
                                        ->where('status', 'taken')
                                        ->first(function($log) {
                                            return $log->created_at->toDateString() == now()->toDateString();
                                        });
                                @endphp

                                @if ($log)
                                    <div class="mt-3 bg-green-50 border border-green-200 rounded px-3 py-2 text-xs text-green-700 font-medium">
                                        ✓ Sudah diminum
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('app.schedules.take', $schedule->id) }}" class="mt-3">
                                        @csrf
                                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded transition">
                                            Tandai Sudah Diminum
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="mt-3 text-xs text-slate-500 italic">
                                    Jadwal mendatang pada {{ $formattedTime }}
                                </div>
                            @endif

                            <!-- Edit & Delete Buttons -->
                            <div class="flex gap-2 mt-3 pt-3 border-t border-slate-200">
                                <a href="{{ route('app.schedules.edit', $schedule) }}" class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 rounded text-center text-xs font-medium hover:bg-blue-200 transition">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('app.schedules.set-non-active', $schedule) }}" style="flex: 1;" onsubmit="return confirm('Non-aktifkan jadwal?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="w-full px-3 py-2 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background-color: #16bac5; background-color: var(--primary-color);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-2">Tidak Ada Jadwal</h2>
                <p class="text-slate-600 mb-6">Anda belum memiliki jadwal minum obat. Silakan buat jadwal baru.</p>
                <a href="{{ route('app.schedules.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition">
                    Buat Jadwal
                </a>
            </div>
        @endforelse
    </div>
</div>

<x-mobile-bottom-nav active="profile" />

@endsection
