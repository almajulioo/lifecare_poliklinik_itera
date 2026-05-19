@extends('admin.layouts.app')

@section('title', 'Edit Data Pasien')
@section('page_title', 'Edit Pasien')

@section('content')
<div class="space-y-8 max-w-4xl">
    <!-- Page Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Edit Data Pasien</h2>
        <p class="text-sm text-gray-600 mt-1">Perbarui informasi pasien poliklinik</p>
    </div>

    <!-- Form Card -->
    <x-admin.card title="Informasi Pasien">
        <form method="POST" action="{{ route('admin.clinic-patients.update', $patient) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info Section -->
            <div class="space-y-4">
                <h3 class="font-semibold text-gray-900">Data Dasar Pasien</h3>
                
                <!-- Nama Pasien -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Pasien <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $patient->name) }}"
                        placeholder="Masukkan nama lengkap pasien"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('name') border-red-500 focus:ring-red-500 @enderror"
                        required
                    />
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Identity Number -->
                <div>
                    <label for="identity_number" class="block text-sm font-medium text-gray-700 mb-1">
                        Nomor Identitas (NIM/NIP/No. Identitas)
                    </label>
                    <input 
                        type="text"
                        id="identity_number"
                        name="identity_number"
                        value="{{ old('identity_number', $patient->user?->nim ?? $patient->identity_number) }}"
                        placeholder="Masukkan nomor identitas"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('identity_number') border-red-500 focus:ring-red-500 @enderror"
                    />
                    @error('identity_number')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    @if($patient->user?->nim)
                        <p class="text-xs text-gray-500 mt-1">💡 NIM dari akun aplikasi pengguna - dapat diedit jika diperlukan</p>
                    @endif
                </div>

                <!-- Program Studi -->
                <div>
                    <label for="prodi" class="block text-sm font-medium text-gray-700 mb-1">
                        Program Studi
                    </label>
                    <input 
                        type="text"
                        id="prodi"
                        name="prodi"
                        value="{{ old('prodi', $prodi) }}"
                        placeholder="Program studi (hanya baca dari akun aplikasi)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50"
                        readonly
                    />
                    @if($prodi)
                        <p class="text-xs text-gray-500 mt-1">💡 Prodi dari akun aplikasi pengguna (read-only)</p>
                    @endif
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori Pasien <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="category"
                        name="category"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('category') border-red-500 focus:ring-red-500 @enderror"
                        required
                    >
                        <option value="">-- Pilih Kategori --</option>
                        <option value="mahasiswa" {{ old('category', $patient->category) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="pegawai" {{ old('category', $patient->category) == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                        <option value="umum" {{ old('category', $patient->category) == 'umum' ? 'selected' : '' }}>Umum</option>
                    </select>
                    @error('category')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Contact Info Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Informasi Kontak</h3>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input 
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $patient->user?->email ?? $patient->email) }}"
                        placeholder="Alamat email (opsional)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('email') border-red-500 focus:ring-red-500 @enderror"
                    />
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Jika pasien memiliki akun aplikasi, email akan otomatis didapatkan dari akun</p>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Nomor Telepon
                    </label>
                    <input 
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $patient->user?->phone ?? $patient->phone) }}"
                        placeholder="Nomor telepon (opsional)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('phone') border-red-500 focus:ring-red-500 @enderror"
                    />
                    @error('phone')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Personal Info Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Informasi Pribadi</h3>
                
                <!-- Age -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="age" class="block text-sm font-medium text-gray-700 mb-1">
                            Usia (Tahun)
                        </label>
                        <input 
                            type="number"
                            id="age"
                            name="age"
                            value="{{ old('age', $age) }}"
                            min="1"
                            max="150"
                            placeholder="Contoh: 20"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('age') border-red-500 focus:ring-red-500 @enderror"
                        />
                        @error('age')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Kelamin
                        </label>
                        <select 
                            id="gender"
                            name="gender"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                   @error('gender') border-red-500 focus:ring-red-500 @enderror"
                        >
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="laki-laki" {{ old('gender', $gender) == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="perempuan" {{ old('gender', $gender) == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Medical Conditions Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Kondisi Medis</h3>
                <p class="text-sm text-gray-600">Daftar kondisi medis pasien (jika ada)</p>
                
                <!-- Medical Conditions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Kondisi Medis
                    </label>
                    <div id="medicalConditionsContainer" class="space-y-3">
                        @if(old('medical_conditions') != null)
                            @foreach(old('medical_conditions') as $index => $condition)
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
                        @elseif($medicalConditions && count($medicalConditions) > 0)
                            @foreach($medicalConditions as $index => $condition)
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
                </div>
            </div>

            <!-- Notes Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Catatan Medis</h3>
                
                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Catatan Tambahan
                    </label>
                    <textarea 
                        id="notes"
                        name="notes"
                        rows="4"
                        placeholder="Masukkan catatan medis tambahan (opsional)"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               @error('notes') border-red-500 focus:ring-red-500 @enderror"
                    >{{ old('notes', $notes) }}</textarea>
                    @error('notes')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Section -->
            <div class="space-y-4 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-900">Status Pasien</h3>
                <p class="text-sm text-gray-600">Status ditentukan secara otomatis berdasarkan jadwal minum obat pasien</p>
                
                <!-- Status Display -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status Saat Ini
                    </label>
                    <div class="flex items-center gap-2 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-semibold text-gray-900">
                                @if($patient->hasActiveMedicationSchedule())
                                    <span class="text-green-700">Aktif</span>
                                @else
                                    <span class="text-gray-700">Tidak Aktif</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-600">
                                @if($patient->hasActiveMedicationSchedule())
                                    Pasien memiliki jadwal minum obat yang masih berlaku
                                @else
                                    @if($patient->isAppUser())
                                        Pasien tidak memiliki jadwal minum obat aktif
                                    @else
                                        Pasien tidak punya akun aplikasi/tidak terdaftar di sistem obat
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Hidden Status Input (untuk form submission) -->
                <input type="hidden" name="status" value="{{ $patient->getAutomaticStatus() }}" />
                
                <!-- Note for manual override (optional) -->
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <strong>💡 Catatan:</strong> Status pasien diupdate otomatis berdasarkan jadwal minum obat. Jika Anda membuat/menghapus jadwal obat, status akan otomatis berubah.
                    </p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Simpan Perubahan
                </button>
                <a href="{{ route('admin.clinic-patients.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Batal
                </a>
            </div>
        </form>
    </x-admin.card>
</div>

<script>
// Menyimpan data awal pasien sebelum perubahan user selection
let initialPatientData = {
    email: '',
    phone: '',
};

// Simpan data awal saat form pertama kali load
document.addEventListener('DOMContentLoaded', function() {
    initialPatientData.email = document.getElementById('email').value || '';
    initialPatientData.phone = document.getElementById('phone').value || '';
});

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
        alert('Minimal harus ada satu kondisi medis');
    }
}
</script>
@endsection

