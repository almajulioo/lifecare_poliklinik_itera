@extends('layouts.app_mobile')

@section('content')

<div class="pb-20 bg-white min-h-screen">
    {{-- HEADER --}}
    <div class="bg-white px-4 pt-4 pb-4 border-b border-gray-100 sticky top-0 z-10">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('app.medications.index') }}" class="text-gray-500 hover:text-gray-700">
                ← Kembali
            </a>
            <h1 class="text-lg font-bold text-gray-900">Edit Obat</h1>
        </div>
        <p class="text-xs text-gray-500 pl-8">Perbarui informasi obat Anda</p>
    </div>

    <div class="px-4 pt-6 space-y-4">

        {{-- INFO BOX --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-xs text-blue-700">
                <strong>💡 Tip:</strong> Perbarui informasi obat sesuai kebutuhan Anda. Perubahan akan langsung diterapkan ke jadwal yang menggunakan obat ini.
            </p>
        </div>

        {{-- FORM --}}
        <form action="{{ route('app.medicines.update', $medicine) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- NAMA OBAT --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Obat</label>
                <input 
                    type="text" 
                    name="name" 
                    required
                    placeholder="Contoh: Vitamin B12"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                    value="{{ old('name', $medicine->name) }}"
                >
                @error('name')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- DOSIS --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Dosis</label>
                <input 
                    type="text" 
                    name="dose" 
                    required
                    placeholder="Contoh: 500"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition"
                    value="{{ old('dose', $medicine->dose) }}"
                >
                @error('dose')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- SATUAN --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Satuan</label>
                <select 
                    name="unit" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition bg-white"
                >
                    <option value="">-- Pilih Satuan --</option>
                    <option value="mg" {{ old('unit', $medicine->unit) == 'mg' ? 'selected' : '' }}>mg (miligram)</option>
                    <option value="g" {{ old('unit', $medicine->unit) == 'g' ? 'selected' : '' }}>g (gram)</option>
                    <option value="ml" {{ old('unit', $medicine->unit) == 'ml' ? 'selected' : '' }}>ml (mililiter)</option>
                    <option value="liter" {{ old('unit', $medicine->unit) == 'liter' ? 'selected' : '' }}>liter</option>
                    <option value="tablet" {{ old('unit', $medicine->unit) == 'tablet' ? 'selected' : '' }}>tablet</option>
                    <option value="kapsul" {{ old('unit', $medicine->unit) == 'kapsul' ? 'selected' : '' }}>kapsul</option>
                    <option value="butir" {{ old('unit', $medicine->unit) == 'butir' ? 'selected' : '' }}>butir</option>
                    <option value="tetes" {{ old('unit', $medicine->unit) == 'tetes' ? 'selected' : '' }}>tetes</option>
                    <option value="sendok" {{ old('unit', $medicine->unit) == 'sendok' ? 'selected' : '' }}>sendok</option>
                    <option value="ampul" {{ old('unit', $medicine->unit) == 'ampul' ? 'selected' : '' }}>ampul</option>
                </select>
                @error('unit')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- CATATAN --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Catatan (Opsional)</label>
                <textarea 
                    name="notes" 
                    rows="3"
                    placeholder="Contoh: Minum setelah makan, efek samping: pusing"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition resize-none"
                >{{ old('notes', $medicine->notes) }}</textarea>
                @error('notes')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- PREVIEW CARD --}}
            <div class="mt-6 rounded-lg border-2 border-gray-200 p-4 bg-gray-50">
                <p class="text-xs text-gray-500 mb-2">Pratinjau:</p>
                <div class="bg-white rounded-lg p-3 border border-gray-200">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-lg">💊</div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-sm text-gray-900">
                                <span class="medicine-name">{{ $medicine->name }}</span>
                            </h3>
                            <p class="text-xs text-gray-600 mt-1">
                                <span class="medicine-dose">{{ $medicine->dose }}</span> 
                                <span class="medicine-unit">{{ $medicine->unit }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-2 medicine-notes" @if(!$medicine->notes) style="display:none;" @endif>{{ $medicine->notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BUTTONS --}}
            <div class="flex gap-2 mt-8 pb-24">
                <a href="{{ route('app.medications.index') }}" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition text-center">
                    Batal
                </a>
                <button type="submit" class="flex-1 px-4 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition">
                    ✓ Simpan Perubahan
                </button>
            </div>
        </form>

    </div>

</div>

{{-- Mobile Bottom Navigation --}}
<x-mobile-bottom-nav active="medications" />

<script>
    // Update preview as user types
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.querySelector('input[name="name"]');
        const doseInput = document.querySelector('input[name="dose"]');
        const unitSelect = document.querySelector('select[name="unit"]');
        const notesInput = document.querySelector('textarea[name="notes"]');

        const previewName = document.querySelector('.medicine-name');
        const previewDose = document.querySelector('.medicine-dose');
        const previewUnit = document.querySelector('.medicine-unit');
        const previewNotes = document.querySelector('.medicine-notes');

        function updatePreview() {
            previewName.textContent = nameInput.value || 'Nama Obat';
            previewDose.textContent = doseInput.value || '500';
            previewUnit.textContent = unitSelect.value || 'mg';
            
            if (notesInput.value) {
                previewNotes.textContent = notesInput.value;
                previewNotes.style.display = 'block';
            } else {
                previewNotes.style.display = 'none';
            }
        }

        nameInput.addEventListener('input', updatePreview);
        doseInput.addEventListener('input', updatePreview);
        unitSelect.addEventListener('change', updatePreview);
        notesInput.addEventListener('input', updatePreview);
    });
</script>

@endsection
