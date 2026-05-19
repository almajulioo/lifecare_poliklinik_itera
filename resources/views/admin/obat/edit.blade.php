@extends('admin.layouts.app')

@section('title', 'Edit Obat')
@section('page_title', 'Edit Obat')

@section('content')
<div class="space-y-8">
    <!-- Back Button -->
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.obat.index') }}" class="text-blue-600 hover:text-blue-700 font-medium flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
    </div>

    <!-- Form Section -->
    <x-admin.card>
        <h2 class="text-xl font-bold text-gray-900 mb-6">Edit Obat: <span class="text-blue-600">{{ $medicine->name }}</span></h2>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="text-red-900 font-semibold mb-2">Terdapat Kesalahan:</h3>
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/admin/obat/{{ $medicine->id }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Obat -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Obat <span class="text-red-600">*</span></label>
                <input 
                    type="text" 
                    name="name" 
                    value="{{ $medicine->name }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                    required 
                />
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Row: Dosis and Unit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dosis -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dosis <span class="text-red-600">*</span></label>
                    <input 
                        type="text" 
                        name="dose" 
                        value="{{ $medicine->dose }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                        required 
                    />
                    @error('dose')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Satuan</label>
                    <select name="unit" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Pilih Satuan --</option>
                        <option value="mg" {{ $medicine->unit == 'mg' ? 'selected' : '' }}>mg (Miligram)</option>
                        <option value="g" {{ $medicine->unit == 'g' ? 'selected' : '' }}>g (Gram)</option>
                        <option value="ml" {{ $medicine->unit == 'ml' ? 'selected' : '' }}>ml (Mililiter)</option>
                        <option value="tablet" {{ $medicine->unit == 'tablet' ? 'selected' : '' }}>Tablet</option>
                        <option value="kapsula" {{ $medicine->unit == 'kapsula' ? 'selected' : '' }}>Kapsul</option>
                        <option value="botol" {{ $medicine->unit == 'botol' ? 'selected' : '' }}>Botol</option>
                        <option value="sachet" {{ $medicine->unit == 'sachet' ? 'selected' : '' }}>Sachet</option>
                        <option value="strip" {{ $medicine->unit == 'strip' ? 'selected' : '' }}>Strip</option>
                    </select>
                    @error('unit')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan/Keterangan</label>
                <textarea 
                    name="notes" 
                    rows="4" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >{{ $medicine->notes }}</textarea>
                @error('notes')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.obat.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    Batal
                </a>
            </div>
        </form>
    </x-admin.card>
</div>
@endsection
