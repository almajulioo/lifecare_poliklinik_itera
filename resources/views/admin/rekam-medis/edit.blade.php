@extends('admin.layouts.app')

@section('title', 'Edit Rekam Medis')
@section('page_title', 'Edit Rekam Medis')

@section('content')
<div class="space-y-8 max-w-4xl">
    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Edit Rekam Medis</h2>
        <p class="text-sm text-gray-600 mt-1">Perbarui kondisi medis dan catatan pasien</p>
    </div>

    <!-- Patient Info Card -->
    <x-admin.card>
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-lg flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                    <p class="text-sm text-gray-600">{{ $user->clinicPatient?->category ?? 'Pengguna Aplikasi' }}</p>
                    <p class="text-sm text-gray-600">NIM: {{ $user->nim ?? '-' }}</p>
                </div>
            </div>
        </div>
    </x-admin.card>

    <!-- Form Card -->
    <x-admin.card title="Edit Kondisi Medis dan Catatan">
        <form method="POST" action="{{ route('admin.rekam-medis.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Medical Conditions Section -->
            <div class="space-y-4">
                <h3 class="font-semibold text-gray-900">Kondisi Medis</h3>
                <p class="text-sm text-gray-600">Daftar kondisi medis pasien</p>
                
                <!-- Medical Conditions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Kondisi Medis
                    </label>
                    <div id="medicalConditionsContainer" class="space-y-3">
                        @if($user->medical_conditions && count($user->medical_conditions) > 0)
                            @foreach($user->medical_conditions as $index => $condition)
                                <div class="flex gap-2 items-start medical-condition-item">
                                    <input 
                                        type="text"
                                        name="medical_conditions[{{ $index }}]"
                                        value="{{ $condition }}"
                                        placeholder="Masukkan kondisi medis (contoh: Anemia, Alergi, dsb)"
                                        class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    <button 
                                        type="button"
                                        onclick="removeMedicalCondition(this)"
                                        class="px-3 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors font-medium text-sm whitespace-nowrap"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="flex gap-2 items-start medical-condition-item">
                                <input 
                                    type="text"
                                    name="medical_conditions[0]"
                                    placeholder="Masukkan kondisi medis (contoh: Anemia, Alergi, dsb)"
                                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <button 
                                    type="button"
                                    onclick="removeMedicalCondition(this)"
                                    class="px-3 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors font-medium text-sm whitespace-nowrap"
                                >
                                    Hapus
                                </button>
                            </div>
                        @endif
                    </div>
                    <button 
                        type="button"
                        onclick="addMedicalCondition()"
                        class="mt-3 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm"
                    >
                        + Tambah Kondisi Medis
                    </button>
                    @error('medical_conditions')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Notes Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Catatan Medis</h3>
                <p class="text-sm text-gray-600">Catatan tambahan tentang kondisi atau riwayat medis pasien</p>
                
                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Catatan
                    </label>
                    <textarea 
                        id="notes"
                        name="notes"
                        rows="6"
                        placeholder="Masukkan catatan medis tambahan..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('notes') border-red-500 focus:ring-red-500 @enderror"
                    >{{ old('notes', $user->notes ?? '') }}</textarea>
                    @error('notes')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.rekam-medis.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Batal
                </a>
            </div>
        </form>
    </x-admin.card>

    <!-- Current Medications Card -->
    @if($user->medicationSchedules->count() > 0)
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Obat Saat Ini</h3>
            <div class="space-y-3">
                @foreach($user->medicationSchedules as $schedule)
                    <div class="flex items-start justify-between p-3 border border-gray-200 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $schedule->medicine->name ?? 'Obat Tidak Ditemukan' }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $schedule->dosage ?? '-' }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $schedule->frequency ?? '-' }}</p>
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
    @endif
</div>

<script>
function addMedicalCondition() {
    const container = document.getElementById('medicalConditionsContainer');
    const index = container.querySelectorAll('.medical-condition-item').length;
    
    const newItem = document.createElement('div');
    newItem.className = 'flex gap-2 items-start medical-condition-item';
    newItem.innerHTML = `
        <input 
            type="text"
            name="medical_conditions[${index}]"
            placeholder="Masukkan kondisi medis (contoh: Anemia, Alergi, dsb)"
            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <button 
            type="button"
            onclick="removeMedicalCondition(this)"
            class="px-3 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors font-medium text-sm whitespace-nowrap"
        >
            Hapus
        </button>
    `;
    
    container.appendChild(newItem);
}

function removeMedicalCondition(button) {
    const container = document.getElementById('medicalConditionsContainer');
    const items = container.querySelectorAll('.medical-condition-item');
    
    if (items.length > 1) {
        button.closest('.medical-condition-item').remove();
    } else {
        // Allow removal of all items - user can have empty conditions
        button.closest('.medical-condition-item').remove();
        
        // If all items are removed, add one empty field
        if (container.querySelectorAll('.medical-condition-item').length === 0) {
            const newItem = document.createElement('div');
            newItem.className = 'flex gap-2 items-start medical-condition-item';
            newItem.innerHTML = `
                <input 
                    type="text"
                    name="medical_conditions[0]"
                    placeholder="Masukkan kondisi medis (contoh: Anemia, Alergi, dsb)"
                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button 
                    type="button"
                    onclick="removeMedicalCondition(this)"
                    class="px-3 py-2.5 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors font-medium text-sm whitespace-nowrap"
                >
                    Hapus
                </button>
            `;
            container.appendChild(newItem);
        }
    }
}
</script>
@endsection
