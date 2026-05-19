@extends('admin.layouts.app')

@section('title', 'Riwayat Pengingat')
@section('page_title', 'Riwayat Pengingat')

@section('content')
<div class="space-y-8">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Monitoring Kepatuhan Pasien</h2>
            <p class="text-sm text-gray-600 mt-1">Pantau dan kelola semua pengingat obat secara real-time</p>
        </div>
        <button class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
        </button>
    </div>
    <!-- Statistics Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <x-admin.card>
            <div class="text-center">
                <p class="text-4xl font-bold text-gray-900">{{ $stats['today'] }}</p>
                <p class="text-sm font-medium text-gray-600 mt-2">Total Hari Ini</p>
            </div>
        </x-admin.card>

        <x-admin.card>
            <div class="text-center">
                <p class="text-4xl font-bold text-green-600">{{ $stats['confirmed'] }}</p>
                <p class="text-sm font-medium text-gray-600 mt-2">Terkonfirmasi</p>
            </div>
        </x-admin.card>

        <x-admin.card>
            <div class="text-center">
                <p class="text-4xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                <p class="text-sm font-medium text-gray-600 mt-2">Ditunda</p>
            </div>
        </x-admin.card>

        <x-admin.card>
            <div class="text-center">
                <p class="text-4xl font-bold text-orange-600">{{ $stats['delayed'] }}</p>
                <p class="text-sm font-medium text-gray-600 mt-2">Terlambat</p>
            </div>
        </x-admin.card>

        <x-admin.card>
            <div class="text-center">
                <p class="text-4xl font-bold text-red-600">{{ $stats['missed'] }}</p>
                <p class="text-sm font-medium text-gray-600 mt-2">Terlewat</p>
            </div>
        </x-admin.card>
    </div>

    <!-- Search and Filter -->
    <form method="GET" class="flex gap-4 items-end">
        <div class="flex-1">
            <input 
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Cari berdasarkan nama pasien, obat, atau waktu..."
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
            />
        </div>
        <input 
            type="text"
            name="time"
            placeholder="hh/mm"
            class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm w-24"
        />
        <select name="status" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700 text-sm">
            <option value="all">Status</option>
            <option value="taken" {{ $status == 'taken' ? 'selected' : '' }}>Diminum</option>
            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Tertunda</option>
            <option value="delayed" {{ $status == 'delayed' ? 'selected' : '' }}>Terlambat</option>
            <option value="missed" {{ $status == 'missed' ? 'selected' : '' }}>Terlewat</option>
        </select>
        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium whitespace-nowrap">
            Cari
        </button>
        <a href="{{ route('admin.riwayat.index') }}" class="px-6 py-2.5 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors font-medium whitespace-nowrap">
            Reset
        </a>
    </form>

    <!-- Reminder Table -->
    <x-admin.card>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">WAKTU</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">PASIEN</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">OBAT</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">DOSIS</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">STATUS</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">WAKTU RESPON</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    @php
                                        $times = $log->schedule->time ? explode(',', $log->schedule->time) : [];
                                        $displayTime = count($times) > 0 ? trim($times[0]) : '-';
                                        if ($displayTime != '-') {
                                            try {
                                                $displayTime = \Carbon\Carbon::parse($displayTime)->format('H:i');
                                            } catch (\Exception $e) {
                                                $displayTime = '-';
                                            }
                                        }
                                    @endphp
                                    {{ $displayTime }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900">{{ $log->schedule->user->name }}</p>
                                        <p class="text-xs text-gray-500">NIM {{ $log->schedule->user->nim ?? '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $log->schedule->medicine->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $log->schedule->dosage }} {{ $log->schedule->unit ?? 'mg' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->status == 'taken')
                                        <x-admin.badge color="green">Diminum</x-admin.badge>
                                    @elseif($log->status == 'pending')
                                        <x-admin.badge color="blue">Tertunda</x-admin.badge>
                                    @elseif($log->status == 'delayed')
                                        <x-admin.badge color="yellow">Terlambat</x-admin.badge>
                                    @elseif($log->status == 'missed')
                                        <x-admin.badge color="red">Terlewat</x-admin.badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $log->taken_at ? \Carbon\Carbon::parse($log->taken_at)->format('H:i') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-600 text-sm">
                                    Belum ada riwayat pengingat
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between text-sm">
                    <p class="text-gray-600">Menampilkan 1-15 dari 120 reminders</p>
                    <div class="flex gap-2">
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Sebelumnya</button>
                        <button class="px-3 py-1 bg-blue-600 text-white rounded-lg">1</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">2</button>
                        <button class="px-3 py-1 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Selanjutnya</button>
                    </div>
                </div>
            @endif
        </x-admin.card>
</div>
@endsection
