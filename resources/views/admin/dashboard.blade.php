@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-8 max-w-7xl">
    <!-- Statistics Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Pengguna -->
        <x-admin.stat-card 
            title="Total Pengguna"
            value="{{ $stats['total_patients'] }}"
            color="blue"
        />

        <!-- Pengingat Aktif -->
        <x-admin.stat-card 
            title="Pengingat Aktif"
            value="{{ $stats['active_schedules'] }}"
            color="green"
        />

        <!-- Pengingat Hari Ini -->
        <x-admin.stat-card 
            title="Pengingat Hari Ini"
            value="{{ $stats['today_reminders'] }}"
            color="yellow"
        />
    </div>

    <!-- Recent Activities -->
    <x-admin.card title="Aktivitas Terbaru">
        <div class="space-y-4">
            @forelse ($recentActivities as $activity)
                <div class="flex items-start gap-4 pb-4 border-b border-gray-100 last:border-b-0 last:pb-0">
                    <!-- Icon based on status -->
                    @if ($activity['status'] == 'taken')
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    @elseif ($activity['status'] == 'skipped')
                        <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</p>
                        <p class="text-sm text-gray-500">{{ $activity['time']->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500">Tidak ada aktivitas terbaru</p>
                </div>
            @endforelse
        </div>
    </x-admin.card>

    <!-- Quick Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Obat -->
        <x-admin.card>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">{{ $stats['total_medicines'] }}</p>
                <p class="text-sm text-gray-600 mt-1">Total Obat</p>
            </div>
        </x-admin.card>

        <!-- Pasien Poliklinik -->
        <x-admin.card>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">{{ $stats['clinic_patients'] }}</p>
                <p class="text-sm text-gray-600 mt-1">Pasien Poliklinik</p>
            </div>
        </x-admin.card>

        <!-- Jadwal Aktif -->
        <x-admin.card>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">{{ $stats['active_schedules'] }}</p>
                <p class="text-sm text-gray-600 mt-1">Jadwal Aktif</p>
            </div>
        </x-admin.card>

        <!-- Tingkat Kepatuhan Hari Ini -->
        <x-admin.card>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">{{ $confirmationRate }}%</p>
                <p class="text-sm text-gray-600 mt-1">Kepatuhan Hari Ini</p>
                <p class="text-xs text-gray-500 mt-2">{{ $todayConfirmed }} dari {{ $todayTotal }} jadwal</p>
            </div>
        </x-admin.card>
    </div>

    <!-- Recent Medicines -->
    @if ($recentMedicines->isNotEmpty())
    <x-admin.card title="Obat Terbaru Ditambahkan">
        <div class="space-y-3">
            @foreach ($recentMedicines as $medicine)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0 last:pb-0">
                    <div>
                        <p class="font-medium text-gray-900">{{ $medicine->name }}</p>
                        <p class="text-sm text-gray-500">{{ $medicine->dose }} {{ $medicine->unit }}</p>
                    </div>
                    <p class="text-xs text-gray-400">{{ $medicine->created_at->diffForHumans() }}</p>
                </div>
            @endforeach
        </div>
    </x-admin.card>
    @endif

    <!-- Recent Schedules -->
    <x-admin.card title="Jadwal Terbaru">
        @if(isset($recentSchedules) && $recentSchedules->isNotEmpty())
            <div class="space-y-3">
                @foreach ($recentSchedules as $schedule)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900">{{ $schedule->user->name ?? 'N/A' }} — {{ $schedule->medicine->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">{{ optional($schedule->created_at)->diffForHumans() }} • {{ $schedule->time ?? '—' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.schedules.edit', $schedule) }}" class="text-sm px-3 py-1 bg-blue-50 text-blue-700 rounded">Edit</a>
                            <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm px-3 py-1 bg-red-50 text-red-700 rounded">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3 text-right">
                <a href="{{ route('admin.schedules.index') }}" class="text-sm text-blue-600 hover:underline">Lihat semua jadwal →</a>
            </div>
        @else
            <div class="text-center py-6 text-gray-500">Belum ada jadwal terakhir. <a href="{{ route('admin.schedules.create') }}" class="text-blue-600">Buat jadwal</a></div>
        @endif
    </x-admin.card>
</div>
@endsection