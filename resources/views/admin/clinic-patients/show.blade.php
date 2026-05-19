@extends('admin.layouts.app')

@section('title', 'Detail Pasien')
@section('page_title', 'Detail Pasien')

@section('content')
<div class="space-y-8 max-w-4xl">
    <!-- Page Header -->
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Detail Pasien</h2>
            <p class="text-sm text-gray-600 mt-1">Informasi lengkap data pasien poliklinik</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.clinic-patients.edit', $patient) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            <a href="{{ route('admin.clinic-patients.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                Kembali
            </a>
        </div>
    </div>

    <!-- Patient Header Card -->
    <x-admin.card>
        <div class="flex items-start gap-6">
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-2xl">
                {{ substr($patient->name, 0, 1) }}
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900">{{ $patient->name }}</h3>
                <p class="text-sm text-gray-600 mt-1">Nomor Identitas: {{ $patient->identity_number ?? '-' }}</p>
                <div class="flex gap-2 mt-3 flex-wrap">
                    @if($patient->category == 'mahasiswa')
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Mahasiswa</span>
                    @elseif($patient->category == 'pegawai')
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">Pegawai</span>
                    @else
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Umum</span>
                    @endif
                    
                    @if($patient->status == 'aktif')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                            Tidak Aktif
                        </span>
                    @endif

                    @if($patient->isAppUser())
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Pengguna Aplikasi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                            Non Pengguna
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </x-admin.card>

    <!-- Patient Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Contact Information -->
        <x-admin.card title="Informasi Kontak">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</p>
                    <p class="text-gray-900 mt-1">{{ $patient->user?->email ?? $patient->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor Telepon</p>
                    <p class="text-gray-900 mt-1">{{ $patient->user?->phone ?? $patient->phone ?? '-' }}</p>
                </div>
            </div>
        </x-admin.card>

        <!-- Personal Information -->
        <x-admin.card title="Informasi Pribadi">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Usia</p>
                        <p class="text-gray-900 mt-1">
                            @if($patient->user?->age)
                                {{ $patient->user->age }} tahun
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Kelamin</p>
                        <p class="text-gray-900 mt-1">
                            @if($patient->user?->gender)
                                {{ ucfirst(str_replace('-', ' ', $patient->user->gender)) }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </x-admin.card>

        <!-- System Information -->
        <x-admin.card title="Informasi Sistem">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Pendaftaran</p>
                    <p class="text-gray-900 mt-1">{{ $patient->created_at->format('d F Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Terakhir Diubah</p>
                    <p class="text-gray-900 mt-1">{{ $patient->updated_at->format('d F Y H:i') }}</p>
                </div>
            </div>
        </x-admin.card>
    </div>

    <!-- Linked User Information (if available) -->
    @if($patient->user)
        <x-admin.card title="Akun Pengguna Aplikasi">
            <div class="space-y-4">
                <div class="flex items-start gap-4 pb-4 border-b border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-semibold text-sm">
                        {{ substr($patient->user->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $patient->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $patient->user->email }}</p>
                        <p class="text-sm text-gray-600">
                            @if($patient->user->role_user == 'mahasiswa')
                                <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Mahasiswa</span>
                            @elseif($patient->user->role_user == 'pegawai')
                                <span class="inline-flex px-2 py-1 rounded text-xs font-semibold bg-purple-100 text-purple-700">Pegawai</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">NIM/NIP</p>
                        <p class="text-gray-900 mt-1">{{ $patient->user->nim ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Program Studi</p>
                        <p class="text-gray-900 mt-1">{{ $patient->user->prodi ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </x-admin.card>
    @endif

    <!-- Medical Conditions Section -->
    @if($patient->user?->medical_conditions && count($patient->user?->medical_conditions) > 0)
        <x-admin.card title="Kondisi Medis">
            <div class="space-y-2">
                @foreach($patient->user->medical_conditions as $condition)
                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200">
                        <span class="text-blue-600 font-bold">→</span>
                        <span class="text-gray-700">{{ $condition }}</span>
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    @endif

    <!-- Medical Notes Section -->
    @if($patient->user?->notes)
        <x-admin.card title="Catatan Medis">
            <div class="prose prose-sm max-w-none">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $patient->user->notes }}</p>
            </div>
        </x-admin.card>
    @endif
</div>
@endsection
