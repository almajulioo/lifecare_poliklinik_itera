@extends('admin.layouts.app')

@section('title', 'Manajemen Pasien Poliklinik')
@section('page_title', 'Manajemen Pasien')

@section('content')
<div class="space-y-8">
    <!-- Page Header dengan Deskripsi -->
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Manajemen Pasien Poliklinik</h2>
            <p class="text-sm text-gray-600 mt-1">Kelola data pasien poliklinik, termasuk pengguna aplikasi dan non-pengguna</p>
        </div>
        <div class="flex gap-3">
            <!-- Laporan PDF Button -->
            <a href="{{ route('admin.clinic-patients.report-pdf', ['month' => request('month', now()->format('Y-m'))]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                Laporan PDF
            </a>
            
            <a href="{{ route('admin.clinic-patients.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pasien
            </a>
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Pasien Poliklinik -->
        <x-admin.card>
            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-600">Total Pasien Poliklinik</p>
                <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            </div>
        </x-admin.card>

        <!-- Pengguna Aplikasi -->
        <x-admin.card>
            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-600">Pengguna Aplikasi</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['app_users'] }}</p>
            </div>
        </x-admin.card>

        <!-- Non Pengguna Aplikasi -->
        <x-admin.card>
            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-600">Non Pengguna Aplikasi</p>
                <p class="text-3xl font-bold text-orange-600">{{ $stats['non_app_users'] }}</p>
            </div>
        </x-admin.card>

        <!-- Pasien Aktif Hari Ini -->
        <x-admin.card>
            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-600">Pasien Aktif</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['active_today'] }}</p>
            </div>
        </x-admin.card>
    </div>

    <!-- Search and Filter -->
    <div class="space-y-4">
        <!-- Search Bar with Filter Button -->
        <div class="flex gap-4 items-end">
            <form method="GET" action="{{ route('admin.clinic-patients.index') }}" class="flex gap-4 flex-1">
                <input 
                    type="text" 
                    name="search"
                    placeholder="Cari berdasarkan nama, identitas, email, atau nomor HP..."
                    value="{{ $search ?? '' }}"
                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button type="submit" class="px-4 py-2.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors font-medium whitespace-nowrap">
                    Cari
                </button>
            </form>
            
            <!-- Filter Button -->
            <button 
                type="button"
                onclick="toggleFilterPanel()"
                class="px-4 py-2.5 border border-gray-300 text-gray-700 bg-white rounded hover:bg-gray-50 transition-colors font-medium whitespace-nowrap flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
        </div>

        <!-- Filter Panel -->
        <div id="filterPanel" class="hidden bg-white border border-gray-300 rounded-lg p-6 shadow-sm space-y-4">
            <form method="GET" action="{{ route('admin.clinic-patients.index') }}" class="space-y-4" id="filterForm">
                <!-- Hidden search input to preserve search -->
                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Filter Bulan/Tanggal -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Bulan Pendataan</label>
                        <input 
                            type="month"
                            name="month"
                            value="{{ request('month', '') }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>

                    <!-- Filter Kategori -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Kategori Pasien</label>
                        <select 
                            name="category"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700 bg-white cursor-pointer">
                            <option value="all">Semua Kategori</option>
                            <option value="mahasiswa" {{ $category == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="pegawai" {{ $category == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                            <option value="umum" {{ $category == 'umum' ? 'selected' : '' }}>Umum</option>
                        </select>
                    </div>

                    <!-- Filter Status Aplikasi -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Status Aplikasi</label>
                        <select 
                            name="app_user"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700 bg-white cursor-pointer">
                            <option value="all">Semua Status</option>
                            <option value="using" {{ $app_user == 'using' ? 'selected' : '' }}>Menggunakan Aplikasi</option>
                            <option value="not_using" {{ $app_user == 'not_using' ? 'selected' : '' }}>Tidak Menggunakan</option>
                        </select>
                    </div>

                    <!-- Filter Status Pasien -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Status Pasien</label>
                        <select 
                            name="status"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-700 bg-white cursor-pointer">
                            <option value="all">Semua Status</option>
                            <option value="aktif" {{ $status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="tidak_aktif" {{ $status == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                    <button 
                        type="button"
                        onclick="resetFilters()"
                        class="px-6 py-2.5 border border-gray-300 text-gray-700 bg-white rounded hover:bg-gray-50 transition-colors font-medium">
                        Reset
                    </button>
                    <button 
                        type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors font-medium">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Patients Table -->
    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">NAMA PASIEN</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">KATEGORI</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">KONTAK</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">STATUS</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">TANGGAL PENDATAAN</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($patients as $patient)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ substr($patient->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $patient->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($patient->category == 'mahasiswa')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        Mahasiswa
                                    </span>
                                @elseif($patient->category == 'pegawai')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                                        Pegawai
                                    </span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                        Umum
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="space-y-1">
                                    @if($patient->email)
                                        <p>{{ $patient->email }}</p>
                                    @endif
                                    @if(!$patient->email)
                                        <p class="text-gray-400">-</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($patient->status == 'aktif')
                                    <span class="inline-flex items-center gap-2 text-sm font-medium">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 text-sm font-medium">
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $patient->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.clinic-patients.show', $patient) }}" title="Detail" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.clinic-patients.edit', $patient) }}" title="Edit" class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.clinic-patients.destroy', $patient) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus pasien ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                                Belum ada data pasien
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($patients->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <p class="text-sm text-gray-600">Menampilkan {{ $patients->firstItem() ?? 0 }}-{{ $patients->lastItem() ?? 0 }} dari {{ $patients->total() }} pasien</p>
                <div class="flex gap-2">
                    {{ $patients->links() }}
                </div>
            </div>
        @endif
    </x-admin.card>
</div>

<script>
function toggleFilterPanel() {
    const filterPanel = document.getElementById('filterPanel');
    filterPanel.classList.toggle('hidden');
}

function resetFilters() {
    // Reset all filter inputs to default values
    document.querySelector('input[name="month"]').value = '';
    document.querySelector('select[name="category"]').value = 'all';
    document.querySelector('select[name="app_user"]').value = 'all';
    document.querySelector('select[name="status"]').value = 'all';
    
    // Submit the form to apply reset
    document.getElementById('filterForm').submit();
}

// Close filter panel when clicking outside
document.addEventListener('click', function(event) {
    const filterPanel = document.getElementById('filterPanel');
    const filterButton = event.target.closest('button[onclick="toggleFilterPanel()"]');
    
    if (!filterPanel.contains(event.target) && !filterButton) {
        filterPanel.classList.add('hidden');
    }
});
</script>

@endsection
