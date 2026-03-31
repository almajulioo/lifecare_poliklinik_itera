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
            <a href="{{ route('admin.rekam-medis.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                Tambah Catatan
            </a>
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
                    <!-- Sarah Johnson -->
                    <a href="?user_id=1" class="flex items-start gap-3 p-3 rounded-lg transition-colors bg-blue-50 border-l-4 border-blue-600">
                        <div class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0 mt-1"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">Sarah Johnson</p>
                            <p class="text-xs text-gray-600">NIM: 1221140068</p>
                            <div class="flex gap-2 mt-2">
                                <x-admin.badge color="red">Anemia</x-admin.badge>
                                <x-admin.badge color="orange">Vertigo</x-admin.badge>
                            </div>
                        </div>
                    </a>

                    <!-- Michael Brown -->
                    <a href="?user_id=2" class="flex items-start gap-3 p-3 rounded-lg transition-colors hover:bg-gray-50">
                        <div class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0 mt-1"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">Michael Brown</p>
                            <p class="text-xs text-gray-600">NIM: 1221140073</p>
                            <div class="flex gap-2 mt-2">
                                <x-admin.badge color="blue">Alergi</x-admin.badge>
                            </div>
                        </div>
                    </a>

                    <!-- Emma Davis -->
                    <a href="?user_id=3" class="flex items-start gap-3 p-3 rounded-lg transition-colors hover:bg-gray-50">
                        <div class="w-2 h-2 rounded-full bg-yellow-500 flex-shrink-0 mt-1"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">Emma Davis</p>
                            <p class="text-xs text-gray-600">NIM: 1221140069</p>
                            <div class="flex gap-2 mt-2">
                                <x-admin.badge color="yellow">Gastritis Kronis</x-admin.badge>
                            </div>
                        </div>
                    </a>

                    <!-- James Wilson -->
                    <a href="?user_id=4" class="flex items-start gap-3 p-3 rounded-lg transition-colors hover:bg-gray-50">
                        <div class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0 mt-1"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">James Wilson</p>
                            <p class="text-xs text-gray-600">NIM: 1221140070</p>
                            <div class="flex gap-2 mt-2">
                                <x-admin.badge color="green">Flu Ringan</x-admin.badge>
                                <x-admin.badge color="blue">Saki Kepala</x-admin.badge>
                            </div>
                        </div>
                    </a>

                    <!-- Olivia Martinez -->
                    <a href="?user_id=5" class="flex items-start gap-3 p-3 rounded-lg transition-colors hover:bg-gray-50">
                        <div class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0 mt-1"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">Olivia Martinez</p>
                            <p class="text-xs text-gray-600">NIM: 1221140071</p>
                            <div class="flex gap-2 mt-2">
                                <x-admin.badge color="red">Demam Tinggi</x-admin.badge>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Pagination -->
                <div class="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between text-xs">
                    <span class="text-gray-600">Menampilkan 5 dari 5 pasien</span>
                </div>
            </x-admin.card>
        </div>

        <!-- Right Column: Patient Details -->
        <div class="lg:col-span-3">
            <!-- Patient Header Card -->
            <x-admin.card>
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-lg flex-shrink-0">
                            S
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-gray-900">Sarah Johnson</p>
                            <p class="text-sm text-gray-600">Teknis Informatika</p>
                            <p class="text-sm text-gray-600">NIM: 1221140068</p>
                            <div class="flex items-center gap-1 mt-1">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                <span class="text-xs text-green-600 font-medium">Pasien Aktif</span>
                            </div>
                        </div>
                    </div>
                    <button class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex-shrink-0">
                        Edit
                    </button>
                </div>
            </x-admin.card>

                <!-- Kondisi Medis -->
                <x-admin.card>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Kondisi Medis</h3>
                    <div class="space-y-3">
                        <div class="p-3 border-l-4 border-red-500 bg-red-50 rounded">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Anemia</p>
                                    <p class="text-xs text-gray-600 mt-1">Diagnosis: 15 Maret 2025</p>
                                    <p class="text-xs text-gray-600 mt-2">Pasien menderusakan suplemen zat besi harian dan penyesuaian diet. Perlu pemeriksaan ulang kadar Hb setiap 3 bulan.</p>
                                </div>
                                <x-admin.badge color="red">Kronis</x-admin.badge>
                            </div>
                        </div>

                        <div class="p-3 border-l-4 border-orange-500 bg-orange-50 rounded">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Vertigo (Benign Paroxysmal Positional Vertigo / BPPV)</p>
                                    <p class="text-xs text-gray-600 mt-1">Diagnosis: 8 Januari 2025</p>
                                    <p class="text-xs text-gray-600 mt-2">Pasien mengalami gejala berulang tiba-tiba saat perubahan posisi kepala. Dikombinasikan dengan manuver reposisi kanal (Inski Manuver Epley) dan menghindari gerakan kepala mendadak.</p>
                                </div>
                                <x-admin.badge color="orange">Olahraga</x-admin.badge>
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                <!-- Obat Saat Ini -->
                <x-admin.card>
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Obat Saat Ini</h3>
                    <div class="space-y-3">
                        <div class="flex items-start justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Ferrous Fumarate</p>
                                <p class="text-xs text-gray-600 mt-1">Sekali sehari setelah makan pagi</p>
                            </div>
                            <x-admin.badge color="green">Aktif</x-admin.badge>
                        </div>

                        <div class="flex items-start justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Vitamin C (Asam Askorbat)</p>
                                <p class="text-xs text-gray-600 mt-1">Sekali sehari bersama suplemen zat besi</p>
                            </div>
                            <x-admin.badge color="green">Aktif</x-admin.badge>
                        </div>

                        <div class="flex items-start justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Betahistine</p>
                                <p class="text-xs text-gray-600 mt-1">Dua atau tiga kali sehari (sesuai anjuran)</p>
                            </div>
                            <x-admin.badge color="green">Aktif</x-admin.badge>
                        </div>
                    </div>
                </x-admin.card>

                <!-- Pengingat Terbaru and Log Aktivitas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Pengingat Terbaru -->
                    <x-admin.card>
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Pengingat Terbaru</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Ferrous Fumarate 200mg</p>
                                    <p class="text-xs text-gray-600">Jam di 8:00 - Diminum</p>
                                </div>
                                <span class="text-green-600 font-bold text-lg">✓</span>
                            </div>

                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Betahistine 6mg</p>
                                    <p class="text-xs text-gray-600">Jam di 11:00 - Diminum</p>
                                </div>
                                <span class="text-red-600 font-bold text-lg">✕</span>
                            </div>

                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Vitamin C 100mg</p>
                                    <p class="text-xs text-gray-600">Jam di 8:00 Pagi - Diminum</p>
                                </div>
                                <span class="text-green-600 font-bold text-lg">✓</span>
                            </div>
                        </div>
                    </x-admin.card>

                    <!-- Log Aktivitas -->
                    <x-admin.card>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-lg">📋</span>
                            <h3 class="text-sm font-semibold text-gray-900">Log Aktivitas</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="p-3 border border-gray-200 rounded-lg">
                                <p class="text-sm font-medium text-gray-900">Profil dispensari</p>
                                <p class="text-xs text-gray-600 mt-1">2 jam yang lalu</p>
                            </div>

                            <div class="p-3 border border-gray-200 rounded-lg">
                                <p class="text-sm font-medium text-gray-900">Penggingat obat daftar</p>
                                <p class="text-xs text-gray-600 mt-1">1 hari yang lalu</p>
                            </div>
                        </div>
                    </x-admin.card>
                </div>
        </div>
    </div>
</div>
@endsection
