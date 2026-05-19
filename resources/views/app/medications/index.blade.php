@extends('layouts.app_mobile')

@section('content')

<div class="pb-28 bg-white min-h-screen">
    {{-- HEADER --}}
    <div class="bg-white px-4 pt-4 pb-4 border-b border-gray-100 sticky top-0 z-10">
        <h1 class="text-lg font-bold text-gray-900"> Daftar Obat</h1>
        <p class="text-xs text-gray-500 mt-1">Obat pribadi Anda dan obat dari jadwal yang diberikan</p>
    </div>

    <div class="px-4 pt-4 space-y-4">

        {{-- ADD BUTTON --}}
        <a href="{{ route('app.medicines.create') }}" class="w-full bg-gradient-to-r from-green-500 to-green-500 hover:from-green-500 hover:to-green-600 text-white font-semibold py-3 rounded-lg text-center transition shadow-sm padding px-4">
            Tambah Obat Baru
        </a>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                <p class="text-sm text-green-800">✓ {{ session('success') }}</p>
            </div>
        @endif

        {{-- OBAT PRIBADI SECTION --}}
        @if($allMedicines->count() > 0)
            <div>
                <div class="space-y-2">
                    @foreach($allMedicines as $medicine)
                        <div class="@if($medicine->user_id == auth()->id() && $medicine->source_type == 'PATIENT') bg-blue-50 border border-blue-200 @else bg-gray-50 border border-gray-300 @endif rounded-lg p-3 flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-sm text-gray-900">{{ $medicine->name }}</h3>
                                    @if($medicine->user_id == auth()->id() && $medicine->source_type == 'PATIENT')
                                        <span class="text-xs bg-blue-200 text-blue-800 px-1.5 py-0.5 rounded">Pribadi</span>
                                    @else
                                        <span class="text-xs bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded">Jadwal</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ $medicine->dose }} {{ $medicine->unit }}
                                    @if($medicine->notes)
                                        · {{ substr($medicine->notes, 0, 30) }}...
                                    @endif
                                </p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                @if($medicine->user_id == auth()->id() && $medicine->source_type == 'PATIENT')
                                    <a href="{{ route('app.medicines.edit', $medicine) }}" class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition" title="Edit Obat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('app.medicines.destroy', $medicine) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition" onclick="return confirm('Yakin hapus obat ini?')" title="Hapus Obat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <div class="text-gray-300">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4.5C7.305 4.5 3.273 7.356 1.806 11.226c-.276.642-.276 1.405 0 2.047C3.273 16.644 7.305 19.5 12 19.5c4.695 0 8.727-2.856 10.194-6.726.276-.642.276-1.405 0-2.047C20.727 7.856 16.695 4.5 12 4.5zM12 15a3 3 0 110-6 3 3 0 010 6z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
                <p class="text-sm text-gray-500 mb-3">Anda belum memiliki daftar obat</p>
                <p class="text-xs text-gray-400">Mulai dengan menambahkan obat pertama Anda</p>
            </div>
        @endif

        {{-- INFO --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-4">
            <p class="text-xs text-blue-800">
                <strong>ℹ️ </strong> Daftar menampilkan obat pribadi Anda dan obat dari jadwal yang diberikan. Anda hanya dapat mengedit obat pribadi Anda sendiri.
            </p>
        </div>

    </div>

</div>

{{-- Mobile Bottom Navigation --}}
<x-mobile-bottom-nav active="medications" />

@endsection
