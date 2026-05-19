@extends('admin.layouts.app')

@section('title', 'Manajemen Obat')
@section('page_title', 'Obat')

@section('content')
<div class="space-y-8">
    <!-- Header with Description and Add Button -->
    <div>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Basis Data Obat</h1>
                <p class="text-sm text-gray-600 mt-1">Kelola daftar obat yang tersedia dalam sistem</p>
            </div>
            <a href="{{ route('admin.obat.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                Tambah Obat
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-admin.stat-card 
            title="Total Obat"
            value="{{ \App\Models\Medicine::count() }}"
            color="blue"
        />

        <x-admin.stat-card 
            title="Baru Ditambahkan (30 Hari)"
            value="{{ \App\Models\Medicine::where('created_at', '>=', now()->subDays(30))->count() }}"
            color="green"
        />
    </div>

    <!-- Search and Filter Section -->
    <x-admin.card>
        <form method="GET" action="{{ route('admin.obat.index') }}" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Obat</label>
                <x-admin.input
                    type="text"
                    name="search"
                    placeholder="Cari nama obat..."
                    value="{{ $search ?? '' }}"
                />
            </div>

            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="latest" {{ ($sort ?? 'latest') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ ($sort ?? 'latest') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    Cari
                </button>
                <a href="{{ route('admin.obat.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                    Reset
                </a>
            </div>
        </form>
    </x-admin.card>

    <!-- Medicine Table Card -->
    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">NAMA OBAT</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">DOSIS</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">UNIT</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">CATATAN</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">DITAMBAHKAN</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($medicines as $medicine)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $medicine->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $medicine->dose }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $medicine->unit ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $medicine->notes ? Str::limit($medicine->notes, 50) : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $medicine->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.obat.edit', $medicine) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="/admin/obat/{{ $medicine->id }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus obat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
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
                            <td colspan="6" class="px-6 py-8 text-center text-gray-600">
                                Belum ada obat terdaftar
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($medicines->hasPages())
            <div class="mt-6 flex items-center justify-between px-6">
                <div class="text-sm text-gray-600">
                    Menampilkan {{ $medicines->firstItem() ?? 0 }} hingga {{ $medicines->lastItem() ?? 0 }} dari {{ $medicines->total() }} obat
                </div>
                <div class="flex gap-2">
                    {{ $medicines->links() }}
                </div>
            </div>
        @endif
    </x-admin.card>
</div>
@endsection
