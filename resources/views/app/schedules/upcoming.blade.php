@extends('layouts.app_mobile')

@section('title', 'Jadwal Minum Obat')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white pb-24">
    <!-- Header -->
    <div class="sticky top-0 z-40 bg-white border-b border-slate-200">
        <div class="max-w-2xl mx-auto px-4 py-4">
            <h1 class="text-2xl font-bold text-slate-900">📅 Jadwal Minum Obat</h1>
            <p class="text-sm text-slate-600 mt-1">Jadwal minum obat Anda mendatang</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-4">
        @forelse ($schedulesByDate as $date => $daySchedules)
            <!-- Date Section -->
            <div class="mb-6">
                <!-- Date Header -->
                <div class="mb-3">
                    <div class="flex items-center gap-2">
                        <div class="text-2xl">
                            @if ($date == now()->toDateString())
                                📌
                            @elseif ($date == now()->addDay()->toDateString())
                                ➡️
                            @else
                                📆
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
                                        Frekuensi: {{ $schedule->frequency }}
                                    </p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            @if ($date == now()->toDateString())
                                @php
                                    $log = $schedule->logs
                                        ->where('status', 'taken')
                                        ->first(function($log) {
                                            return $log->created_at->toDateString() === now()->toDateString();
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
                                    Jadwal mendatang
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="text-xl font-bold text-slate-900 mb-2">Tidak Ada Jadwal</h2>
                <p class="text-slate-600 mb-6">Anda belum memiliki jadwal minum obat. Silakan buat jadwal baru.</p>
                <a href="{{ route('app.medicines.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition">
                    Buat Jadwal
                </a>
            </div>
        @endforelse
    </div>
</div>

<x-mobile-bottom-nav active="profile" />

@endsection
