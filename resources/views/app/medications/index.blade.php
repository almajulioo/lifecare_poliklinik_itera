@extends('layouts.app_mobile')

@section('content')

<div class="pb-28 bg-white min-h-screen">
    {{-- HEADER --}}
    <div class="bg-white px-4 pt-4 pb-4 border-b border-gray-100 sticky top-0 z-10">
        <h1 class="text-lg font-bold text-gray-900">📋 Daftar Obat</h1>
        <p class="text-xs text-gray-500 mt-1">Admin medicines + Obat pribadi Anda</p>
    </div>

    <div class="px-4 pt-4 space-y-4">

        {{-- ADD BUTTON --}}
        <a href="{{ route('app.medicines.create') }}" class="w-full bg-gradient-to-r from-green-400 to-green-500 hover:from-green-500 hover:to-green-600 text-white font-semibold py-3 rounded-lg text-center transition shadow-sm">
            ➕ Tambah Obat Baru
        </a>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                <p class="text-sm text-green-800">✓ {{ session('success') }}</p>
            </div>
        @endif

        {{-- OBAT PRIBADI SECTION --}}
        @if($userMedicines->count() > 0)
            <div>
                <h2 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                    <span>🔷 Obat Pribadi Anda</span>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ $userMedicines->count() }}</span>
                </h2>
                <div class="space-y-2">
                    @foreach($userMedicines as $medicine)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold text-sm text-gray-900">{{ $medicine->name }}</h3>
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ $medicine->dose }} {{ $medicine->unit }}
                                    @if($medicine->notes)
                                        · {{ substr($medicine->notes, 0, 30) }}...
                                    @endif
                                </p>
                            </div>
                            <div class="flex gap-1 ml-2">
                                <a href="{{ route('app.medicines.edit', $medicine) }}" class="text-blue-600 hover:text-blue-700" title="Edit">
                                    ✏️
                                </a>
                                <form action="{{ route('app.medicines.destroy', $medicine) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-700" onclick="return confirm('Yakin hapus obat ini?')" title="Delete">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ADMIN MEDICINES SECTION --}}
        <div>
            <h2 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                <span>💊 Obat dari Admin</span>
                @if($adminMedicines->total() > 0)
                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">{{ $adminMedicines->total() }}</span>
                @endif
            </h2>
            
            @forelse($adminMedicines as $medicine)
                <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2 flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-sm text-gray-900">{{ $medicine->name }}</h3>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ $medicine->dose }} {{ $medicine->unit }}
                            @if($medicine->notes)
                                · {{ substr($medicine->notes, 0, 30) }}...
                            @endif
                        </p>
                    </div>
                    <div class="ml-2 text-gray-400">
                        👁️
                    </div>
                </div>
            @empty
                <div class="text-center py-6 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Tidak ada obat dari admin</p>
                </div>
            @endforelse

            {{-- PAGINATION --}}
            @if($adminMedicines->hasPages())
                <div class="mt-4 flex justify-center gap-2">
                    @if($adminMedicines->onFirstPage())
                        <span class="text-xs px-2 py-1 bg-gray-200 text-gray-500 rounded">← Sebelumnya</span>
                    @else
                        <a href="{{ $adminMedicines->previousPageUrl() }}" class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">← Sebelumnya</a>
                    @endif

                    <span class="text-xs px-2 py-1 text-gray-600">
                        {{ $adminMedicines->currentPage() }} / {{ $adminMedicines->lastPage() }}
                    </span>

                    @if($adminMedicines->hasMorePages())
                        <a href="{{ $adminMedicines->nextPageUrl() }}" class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Selanjutnya →</a>
                    @else
                        <span class="text-xs px-2 py-1 bg-gray-200 text-gray-500 rounded">Selanjutnya →</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- INFO --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-4">
            <p class="text-xs text-yellow-800">
                <strong>ℹ️ Info:</strong> Obat pribadi Anda dapat digunakan saat membuat jadwal pengingat. Obat dari admin dapat digunakan jika admin memungkinkan.
            </p>
        </div>

    </div>

</div>

{{-- Mobile Bottom Navigation --}}
<x-mobile-bottom-nav active="medications" />

@endsection
