@extends('admin.layouts.app')

@section('title', 'Rekam Medis')
@section('page_title', 'Rekam Medis')

@section('content')
<div class="space-y-8">
    <!-- Header with Description and Add Button -->
    <div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Catatan Medis</h1>
                <p class="text-sm text-gray-600 mt-1">Lihat dan kelola informasi dan riwayat medis pasien</p>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left Column: Patient List -->
        <div class="lg:col-span-1">
            <x-admin.card>
                <!-- Search -->
                <form method="GET" action="{{ route('admin.rekam-medis.index') }}" class="mb-4">
                    <x-admin.input 
                        type="text" 
                        name="search" 
                        placeholder="Cari Pasien..."
                        value="{{ $search ?? '' }}"
                    />
                </form>

                <!-- Patient List -->
                <div class="space-y-2">
                    @forelse($users as $user)
                        <a href="?user_id={{ $user->id }}" class="flex items-start gap-3 p-3 rounded-lg transition-colors {{ $selectedUser && $selectedUser->id == $user->id ? 'bg-blue-50 border-l-4 border-blue-600' : 'hover:bg-gray-50' }}">
                            <div class="w-2 h-2 rounded-full {{ $user->medicationSchedules && $user->medicationSchedules->count() > 0 ? 'bg-green-500' : 'bg-yellow-500' }} flex-shrink-0 mt-1"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                <p class="text-xs text-gray-600">NIM: {{ $user->nim ?? '-' }}</p>
                                @php
                                    $medicalConditions = is_array($user->medical_conditions) ? $user->medical_conditions : [];
                                @endphp
                                @if(count($medicalConditions) > 0)
                                    <div class="flex gap-2 mt-2 flex-wrap">
                                        @foreach(array_slice($medicalConditions, 0, 2) as $condition)
                                            <x-admin.badge color="blue">{{ $condition }}</x-admin.badge>
                                        @endforeach
                                        @if(count($medicalConditions) > 2)
                                            <x-admin.badge color="gray">+{{ count($medicalConditions) - 2 }}</x-admin.badge>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-xs text-gray-500 mt-2">Tidak ada kondisi medis</p>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-3 text-center">
                            <p class="text-sm text-gray-600">Tidak ada pasien</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    {{ $users->links('pagination::tailwind') }}
                </div>
            </x-admin.card>
        </div>

        <!-- Right Column: Patient Details -->
        <div class="lg:col-span-3">
            @if($selectedUser)
                <!-- Patient Header Card -->
                <x-admin.card>
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-lg flex-shrink-0">
                                {{ strtoupper(substr($selectedUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-lg font-semibold text-gray-900">{{ $selectedUser->name }}</p>
                                <p class="text-sm text-gray-600">{{ $selectedUser->clinicPatient?->category ?? $selectedUser->role_user ?? 'Pengguna' }}</p>
                                <p class="text-sm text-gray-600">NIM: {{ $selectedUser->nim ?? '-' }}</p>
                                <div class="flex items-center gap-1 mt-1">
                                    <span class="w-2 h-2 rounded-full {{ $selectedUser->medicationSchedules && $selectedUser->medicationSchedules->count() > 0 ? 'bg-green-500' : 'bg-yellow-500' }}"></span>
                                    <span class="text-xs {{ $selectedUser->medicationSchedules && $selectedUser->medicationSchedules->count() > 0 ? 'text-green-600' : 'text-yellow-600' }} font-medium">{{ $selectedUser->medicationSchedules && $selectedUser->medicationSchedules->count() > 0 ? 'Pasien Aktif' : 'Tidak Ada Obat' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                <!-- Kondisi Medis -->
                <x-admin.card>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Kondisi Medis</h3>
                    @php
                        $medicalConditions = is_array($selectedUser->medical_conditions) ? $selectedUser->medical_conditions : [];
                    @endphp
                    @if(count($medicalConditions) > 0)
                        <div class="space-y-3">
                            @foreach($medicalConditions as $index => $condition)
                                <div class="p-3 border-l-4 border-blue-500 bg-blue-50 rounded">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $condition }}</p>
                                            <p class="text-xs text-gray-600 mt-1">Kondisi Pasien</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center">
                            <p class="text-sm text-gray-600">Pasien tidak memiliki kondisi medis yang tercatat</p>
                        </div>
                    @endif
                </x-admin.card>

                <!-- Catatan Medis -->
                @if($selectedUser->notes)
                    <x-admin.card>
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Catatan Medis</h3>
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $selectedUser->notes }}</p>
                        </div>
                    </x-admin.card>
                @endif

                <!-- Obat Saat Ini -->
                @if($selectedUser->medicationSchedules && $selectedUser->medicationSchedules->count() > 0)
                    <x-admin.card>
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Obat Saat Ini</h3>
                        <div class="space-y-3">
                            @foreach($selectedUser->medicationSchedules as $schedule)
                                <div class="flex items-start justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $schedule->medicine?->name ?? 'Obat Tidak Ditemukan' }}</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $schedule->medicine?->dose ?? '-' }} {{ $schedule->medicine?->unit ?? '' }} | {{ $schedule->frequency ?? '-' }}</p>
                                    </div>
                                    @if($schedule->is_active)
                                        <x-admin.badge color="green">Aktif</x-admin.badge>
                                    @else
                                        <x-admin.badge color="gray">Tidak Aktif</x-admin.badge>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-admin.card>
                @else
                    <x-admin.card>
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Obat Saat Ini</h3>
                        <div class="p-4 text-center">
                            <p class="text-sm text-gray-600">Tidak ada obat yang dijadwalkan</p>
                        </div>
                    </x-admin.card>
                @endif

                <!-- Riwayat Log Obat -->
                @if($selectedUser->medicationLogs && $selectedUser->medicationLogs->count() > 0)
                    <x-admin.card>
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Riwayat Konsumsi Obat Terbaru</h3>
                        <div class="space-y-3">
                            @foreach($selectedUser->medicationLogs->sortByDesc('created_at')->take(5) as $log)
                                <div class="flex items-start justify-between p-3 border border-gray-200 rounded-lg">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $log->medicationSchedule?->medicine?->name ?? 'Obat Tidak Ditemukan' }}</p>
                                        <p class="text-xs text-gray-600 mt-1">{{ $log->created_at ? $log->created_at->format('d M Y H:i') : '-' }}</p>
                                    </div>
                                    @if($log->status == 'taken' || $log->status == 'completed')
                                        <span class="text-green-600 font-bold text-lg">✓</span>
                                    @elseif($log->status == 'missed')
                                        <span class="text-red-600 font-bold text-lg">✕</span>
                                    @else
                                        <span class="text-gray-400 font-bold text-lg">○</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-admin.card>
                @endif
            @else
                <!-- No Selection Message -->
                <x-admin.card>
                    <div class="py-12 text-center">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <p class="text-gray-600 text-lg font-medium">Pilih Pasien</p>
                        <p class="text-gray-500 text-sm mt-2">Pilih pasien dari daftar untuk melihat rekam medis mereka</p>
                    </div>
                </x-admin.card>
            @endif
        </div>
    </div>
</div>
@endsection
